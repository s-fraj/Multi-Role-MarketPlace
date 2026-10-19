@php
  /** @var \App\Models\Avis $avis */
@endphp
<form id="avis-edit-form-{{ $avis->id }}" class="avis-edit-form" style="display:none; margin-top:14px;" method="POST" action="{{ route('avis.update', $avis) }}" enctype="multipart/form-data">
  @csrf
  @method('PUT')

  <div class="field" style="margin-bottom:12px;">
    <label>Ta note</label>
    @include('partials.star-input', ['suffix' => 'edit-' . $avis->id, 'valeurInitiale' => $avis->note])
  </div>

  <div class="field" style="margin-bottom:12px;">
    <label>Ton commentaire</label>
    <textarea name="commentaire" rows="3" required>{{ $avis->commentaire }}</textarea>
  </div>

  <div class="field" style="margin-bottom:12px;">
    <label>Photos</label>
    <div class="avis-photo-row" id="avis-photo-row-edit-{{ $avis->id }}">
      @foreach ($avis->photos as $photo)
        <div class="avis-photo-slot existing" data-photo-id="{{ $photo->id }}" style="background-image:url('{{ $photo->url() }}');">
          <span class="avis-photo-remove" title="Retirer"><i class="ti ti-x"></i></span>
        </div>
      @endforeach
      <label class="avis-camera-btn" title="Ajouter une photo">
        <i class="ti ti-camera"></i>
        <input type="file" accept="image/*" multiple style="display:none;" id="avis-photos-input-edit-{{ $avis->id }}">
      </label>
    </div>
    <input type="file" name="photos[]" id="avis-photos-real-edit-{{ $avis->id }}" multiple style="display:none;">
    <div class="avis-photos-supprimees-container"></div>
  </div>

  <div style="display:flex; gap:10px;">
    <button type="submit" class="btn btn-primary" style="font-size:13px; padding:8px 16px;">Enregistrer</button>
    <button type="button" class="btn btn-ghost" style="font-size:13px; padding:8px 16px;" onclick="basculerEditionAvis({{ $avis->id }})">Annuler</button>
  </div>
</form>
