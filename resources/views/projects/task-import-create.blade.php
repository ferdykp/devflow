@extends('layouts.app')
@section('title', 'Tugas dari dokumen')
@section('page-heading', $project->name)
@section('content')
@include('projects._workspace_navigation', ['section' => 'prd'])
<div class="max-w-3xl">
    <x-ui.page-heading title="To-do otomatis dari Markdown" description="Unggah PRD atau panduan pengerjaan. DevFlow membuat kelompok pekerjaan, tugas berurutan, dan checklist dari struktur dokumenmu." />
    <x-ui.panel>
        <form action="{{ route('task-imports.store', $project) }}" method="POST" enctype="multipart/form-data" class="space-y-5" data-busy-form>@csrf
            <x-ui.field name="title" label="Judul dokumen" maxlength="200" placeholder="Contoh: Fitur akun pengguna" />
            <div class="space-y-3 rounded-xl border border-dashed border-blue-200 bg-blue-50/40 p-5"><label for="document-file" class="block text-sm font-medium">Atau unggah berkas</label><input id="document-file" name="document_file" type="file" accept=".txt,.md,.markdown,text/plain,text/markdown" @error('document_file') aria-invalid="true" aria-describedby="document-file-error" @enderror class="block w-full min-w-0 rounded-lg border border-zinc-300 bg-white p-3 text-sm aria-invalid:border-red-500 file:mr-3 file:rounded-md file:border-0 file:bg-zinc-100 file:px-3 file:py-2 file:font-medium"><p class="text-xs leading-5 text-zinc-500">TXT atau Markdown, maksimal 200 KB dan 1.000 tugas. Untuk PDF atau Word, salin teksnya ke kolom di atas.</p>@error('document_file')<p id="document-file-error" role="alert" class="text-sm text-red-700">{{ $message }}</p>@enderror</div>
            <p class="text-center text-xs text-zinc-400">atau tempel teks dokumen</p>
            <x-ui.textarea name="content_markdown" label="Isi dokumen" rows="5" maxlength="200000" placeholder="Contoh:&#10;## Akun pengguna&#10;- Buat halaman pendaftaran&#10;  - Validasi email dan kata sandi&#10;- Buat halaman masuk&#10;- Tambahkan tombol keluar" />
            <x-ui.select name="mode" label="Setelah dokumen dibaca" :options="['automatic' => 'Langsung buat to-do list', 'review' => 'Tinjau draf terlebih dahulu']" value="automatic" required />
            <div class="rounded-lg bg-zinc-50 p-4 text-sm leading-6 text-zinc-600"><p>Urutan mengikuti dokumen. Judul bagian menjadi kelompok, langkah utama menjadi tugas, dan subpoin menjadi checklist. Tujuan, latar belakang, dan referensi tetap menjadi konteks. </p><details class="mt-3"><summary class="cursor-pointer font-medium text-zinc-800">Lihat contoh struktur Markdown</summary><pre class="mt-3 overflow-x-auto whitespace-pre-wrap break-words text-xs leading-6"># Proyek Website
## Tahap 1: Persiapan
1. Siapkan proyek Laravel
   - Atur database
   - Jalankan migrasi
## Tahap 2: Fitur utama
### Buat halaman masuk
- Validasi email dan kata sandi
- Uji akun yang tidak valid</pre></details></div>
            <div class="flex flex-wrap gap-3"><x-ui.button data-busy-label="Menyusun to-do list…">Proses dokumen</x-ui.button><x-ui.button :href="route('projects.show', ['project' => $project, 'section' => 'prd'])" variant="secondary">Batal</x-ui.button></div>
        </form>
    </x-ui.panel>
</div>
</div>
@endsection
