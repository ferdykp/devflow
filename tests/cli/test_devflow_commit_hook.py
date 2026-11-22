import io
import json
import os
import pathlib
import subprocess
import sys
import tempfile
import types
import unittest
from importlib.machinery import SourceFileLoader
from urllib.error import URLError
from unittest.mock import patch

HOOK = pathlib.Path(__file__).resolve().parents[2] / 'public' / 'devflow-prepare-commit-msg'
loader = SourceFileLoader('devflow_commit_hook', str(HOOK))
hook = types.ModuleType(loader.name)
loader.exec_module(hook)


class CommitHookTest(unittest.TestCase):
    def make_repo(self, folder, base_url):
        def git(*args):
            return subprocess.run(['git', '-C', folder, *args], check=True, capture_output=True, text=True)

        git('init', '-q')
        git('config', 'user.name', 'Test')
        git('config', 'user.email', 'test@example.com')
        git('config', 'devflow.url', base_url)
        git('config', 'devflow.token', 'dfc_test-token')
        git('config', 'devflow.language', 'en')
        pathlib.Path(folder, 'auth.php').write_text('<?php\nreturn true;\n')
        git('add', 'auth.php')
        return git

    def test_hook_fills_commit_editor_from_the_staged_diff(self):
        with tempfile.TemporaryDirectory() as folder:
            self.make_repo(folder, 'https://devflow.example.test')
            message_path = pathlib.Path(folder, 'COMMIT_EDITMSG')
            message_path.write_text('# Please enter the commit message.\n')
            requests = []

            class FakeResponse:
                def __enter__(self):
                    return self

                def __exit__(self, *args):
                    return False

                def read(self):
                    return json.dumps({'message': 'feat(auth): add password reset'}).encode()

            def fake_urlopen(request, timeout):
                requests.append((request.full_url, request.headers, json.loads(request.data)))
                return FakeResponse()

            original_directory = pathlib.Path.cwd()
            try:
                os.chdir(folder)
                with patch.object(hook, 'urlopen', side_effect=fake_urlopen), patch.object(sys, 'stderr', io.StringIO()):
                    result = hook.main([str(message_path)])
            finally:
                os.chdir(original_directory)

            self.assertEqual(result, 0)
            self.assertTrue(message_path.read_text().startswith('feat(auth): add password reset\n'))
            self.assertEqual(len(requests), 1)
            url, headers, payload = requests[0]
            self.assertEqual(url, 'https://devflow.example.test/api/commit-message')
            self.assertEqual(headers['Authorization'], 'Bearer dfc_test-token')
            self.assertEqual(payload['files'], ['auth.php'])
            self.assertIn('return true', payload['diff'])

    def test_hook_skips_explicit_message_and_does_not_block_on_service_failure(self):
        with tempfile.TemporaryDirectory() as folder:
            self.make_repo(folder, 'https://devflow.example.test')
            message_path = pathlib.Path(folder, 'COMMIT_EDITMSG')
            message_path.write_text('# message\n')
            original_directory = pathlib.Path.cwd()
            try:
                os.chdir(folder)
                with patch.object(sys, 'stderr', io.StringIO()):
                    explicit = hook.main([str(message_path), 'message'])
                self.assertEqual(explicit, 0)
                self.assertEqual(message_path.read_text(), '# message\n')

                with patch.object(hook, 'urlopen', side_effect=URLError('offline')), patch.object(sys, 'stderr', io.StringIO()):
                    failed = hook.main([str(message_path)])
            finally:
                os.chdir(original_directory)

            self.assertEqual(failed, 0)
            self.assertEqual(message_path.read_text(), '# message\n')


if __name__ == '__main__':
    unittest.main()
