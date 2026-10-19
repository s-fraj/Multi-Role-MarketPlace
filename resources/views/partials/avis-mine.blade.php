@php
  /** @var \App\Models\Avis $avis */
@endphp
<div id="avis-mine-display-{{ $avis->id }}">
  @include('partials.avis-item', ['avisItem' => $avis])
  <div class="avis-mine-actions">
    <button type="button" class="btn btn-ghost" style="font-size:13px; padding:6px 14px;" onclick="basculerEditionAvis({{ $avis->id }})"><i class="ti ti-edit"></i> Modifier</button>
    <form method="POST" action="{{ route('avis.destroy', $avis) }}" onsubmit="return confirm('Supprimer ton avis ?');" style="display:inline;">
      @csrf
      @method('DELETE')
      <button type="submit" class="btn btn-ghost" style="font-size:13px; padding:6px 14px; color:var(--terracotta);"><i class="ti ti-trash"></i> Supprimer</button>
    </form>
  </div>
</div>

@include('partials.avis-edit-form', ['avis' => $avis])
