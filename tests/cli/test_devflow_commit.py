import importlib.util
import pathlib
import subprocess
import tempfile
import unittest
from unittest.mock import patch

SCRIPT = pathlib.Path(__file__).resolve().parents[2] / 'tools' / 'devflow-commit.py'
spec = importlib.util.spec_from_file_location('devflow_commit', SCRIPT)
mod = importlib.util.module_from_spec(spec)
spec.loader.exec_module(mod)

class CommitCLITest(unittest.TestCase):
    def test_conventional_commit_validation(self):
        mod.validate_message('feat(project): add status filters')
        for msg in ['invalid', 'feat: ' + 'x' * 80, 'oops: execute this', 'feat: bad\nmessage']:
            with self.assertRaises(mod.CommitError):
                mod.validate_message(msg)

    def test_local_fallback(self):
        self.assertEqual(mod.local_message(['README.md']), 'docs: update documentation')
        self.assertEqual(mod.local_message(['tests/Feature/Test.php']), 'test: update automated tests')

    def test_preview_never_commits(self):
        with tempfile.TemporaryDirectory() as folder:
            def run(*args):
                return subprocess.run(['git', '-C', folder, *args], check=True, stdout=subprocess.PIPE, stderr=subprocess.PIPE)
            run('init', '-q')
            run('config', 'user.name', 'Test')
            run('config', 'user.email', 'test@example.com')
            pathlib.Path(folder, 'README.md').write_text('hello\n')
            run('add', 'README.md')
            with patch.object(mod, 'git', side_effect=lambda *args: subprocess.run(['git', '-C', folder, *args], check=True, text=True, capture_output=True).stdout.strip()):
                self.assertEqual(mod.main([]), 0)
            self.assertNotEqual(subprocess.run(['git', '-C', folder, 'rev-parse', '--verify', 'HEAD'], capture_output=True).returncode, 0)

    def test_ai_requires_consent(self):
        with self.assertRaises(SystemExit):
            mod.main(['--ai'])

if __name__ == '__main__':
    unittest.main()
