@extends('layouts.app')
@section('title',$project->name)
@section('page-heading',$project->name)
@section('content')
@php
    $allowedSections = ['overview', 'tasks', 'resume', 'activity', 'notes', 'backlog', 'milestones', 'scope', 'prd', 'github', 'settings'];
    $requestedSection = request()->query('section', 'overview');
    $section = is_string($requestedSection) && in_array($requestedSection, $allowedSections, true)
        ? $requestedSection
        : 'overview';
@endphp
@include('projects._workspace_navigation', ['section' => $section])
<section class="relative min-w-0 w-full min-h-[400px]" aria-label="Proyek section">

 @if($section==='overview') @include('projects._section_overview')
 @else
  @if($section!=='tasks')<div class="df-section-header [display:flex] [align-items:end] [justify-content:space-between] [gap:20px] [margin-bottom:22px] max-[650px]:[align-items:start] max-[650px]:[flex-direction:column]"><div><p class="df-kicker [text-transform:uppercase] [color:#757983] [font-size:10px] [letter-spacing:.15em] [font-weight:750] [margin-bottom:12px]">{{ $project->name }}</p><h2 class="df-section-title [font-size:24px] [letter-spacing:-.045em] [line-height:1.3] [font-weight:650]" id="current-section-heading">{{ ['backlog'=>'Ide nanti', 'milestones'=>'Tahapan', 'scope'=>'Cakupan', 'prd'=>'Dokumen', 'github'=>'GitHub', 'settings'=>'Pengaturan', 'activity'=>'Aktivitas', 'notes'=>'Catatan', 'resume'=>'Lanjutkan'][$section] ?? ucfirst($section) }}</h2></div></div>@endif
  @include('projects._section_'.$section)
 @endif
 </section></div>
@endsection
