@extends('layouts.app')
@section('title', 'Edit proyek')
@section('page-heading', 'Edit proyek')
@section('content')
<div class="mx-auto max-w-2xl">
    <a href="{{ route('projects.show', $project) }}" class="mb-5 inline-flex min-h-11 items-center gap-2 text-sm text-zinc-500 hover:text-blue-700"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i>Kembali ke tugas</a>
    <x-ui.page-heading title="Edit proyek" :description="$project->name" />
    @include('projects._section_settings')
</div>
@endsection
