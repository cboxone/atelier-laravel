@extends('technician.layout')
@section('title', $i->reference)
@section('content')
<h1>{{ $i->title }}</h1>
<div class="card"><p style="margin:0"><strong>{{ $i->customer->name }}</strong></p>
@if($i->customer->phone)<a class="tel" href="tel:{{ $i->customer->phone }}">Appeler {{ $i->customer->phone }}</a><br>@endif
@if($i->customer->address)<span class="muted">{{ $i->customer->address }}</span><br>@endif
@if($i->equipment)<span class="muted">Équipement : {{ $i->equipment->label }} {{ $i->equipment->brand }} {{ $i->equipment->model }}</span>@endif
@if($i->status === 'planned')<form method="post" action="{{ route('tech.start', $i) }}" style="margin-top:12px">@csrf<button class="btn dark">Démarrer l’intervention</button></form>@endif</div>

<div class="card"><h2>Pièces et travaux</h2>
<table>@forelse($i->lines as $l)<tr><td>{{ $l->quantity }} × {{ $l->description }}</td></tr>@empty<tr><td class="muted">Rien d’ajouté.</td></tr>@endforelse</table>
@if(in_array($i->status, ['planned', 'in_progress']))
<form method="post" action="{{ route('tech.lines', $i) }}" style="margin-top:10px">@csrf
<label>Type<select name="kind" id="kind"><option value="part">Pièce du catalogue</option><option value="labor">Main-d’œuvre</option></select></label>
<label>Pièce<select name="product_id"><option value="">Choisir…</option>@foreach($products as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select></label>
<label>Travail (main-d’œuvre)<input name="description" placeholder="Ex. remplacement du détendeur"></label>
<label>Quantité (heures pour la main-d’œuvre)<input type="number" name="quantity" min="1" max="100" value="1" required></label>
<button class="btn dark">Ajouter</button></form>@endif</div>

@if($i->equipment)
<div class="card"><h2>Fluides frigorigènes</h2><p class="muted" style="margin:0 0 8px">Charge enregistrée : <strong>{{ \App\Support\Mass::gramsToKg($balance) }} kg</strong></p>
<table>@foreach($i->fluidMovements as $m)<tr><td>{{ \App\Models\FluidMovement::OPERATIONS[$m->operation] }} · {{ $m->fluid }}</td><td class="num">{{ $m->quantity_g > 0 ? '+' : '' }}{{ $m->kg }} kg</td></tr>@endforeach</table>
@if(in_array($i->status, ['planned', 'in_progress']))
<form method="post" action="{{ route('tech.fluid', $i) }}" style="margin-top:10px">@csrf
<label>Opération<select name="operation"><option value="charge">Ajout de fluide</option><option value="recovery">Récupération</option><option value="initial">Charge initiale déclarée</option></select></label>
<label>Fluide<input name="fluid" value="{{ old('fluid', $i->equipment->fluid_type) }}" required></label>
<label>Quantité en kilos<input name="kg" inputmode="decimal" placeholder="0,350" required></label>
<button class="btn dark">Inscrire au registre</button></form>@endif</div>@endif

<div class="card"><h2>Photos</h2>
@if($i->photos->isNotEmpty())<div class="photos">@foreach($i->photos as $p)<a href="{{ route('tech.photo', $p) }}"><img src="{{ route('tech.photo', $p) }}" alt="Photo {{ $loop->iteration }} de l’intervention" loading="lazy"></a>@endforeach</div>@endif
@if(in_array($i->status, ['planned', 'in_progress']))
<form method="post" action="{{ route('tech.photos', $i) }}" enctype="multipart/form-data">@csrf
<label>Prendre ou choisir une photo<input type="file" name="photo" accept="image/*" capture="environment" required></label>
<button class="btn line">Envoyer la photo</button></form>@endif</div>

@if(in_array($i->status, ['planned', 'in_progress']))
<div class="card"><h2>Signature du client</h2>
@if($i->signed_at)<p class="muted">Signé par {{ $i->signed_by }} le {{ $i->signed_at->format('d/m/Y H:i') }} (refaire la signature la remplace).</p>@endif
<form method="post" action="{{ route('tech.sign', $i) }}" id="signform">@csrf
<label>Nom du signataire<input name="signed_by" value="{{ old('signed_by', $i->signed_by) }}" required></label>
<canvas id="sig" width="600" height="220" aria-label="Zone de signature : le client signe avec le doigt"></canvas>
<input type="hidden" name="signature" id="sigdata">
<p id="sigerr" class="alert" role="alert" style="display:none;margin-top:8px">Le client doit signer dans le cadre.</p>
<div style="display:flex;gap:8px;margin-top:8px"><button type="button" class="btn line" id="clear">Effacer</button><button class="btn dark">Enregistrer la signature</button></div></form></div>

<div class="card"><h2>Terminer</h2>
<form method="post" action="{{ route('tech.finish', $i) }}">@csrf
<label>Travaux réalisés<textarea name="work_done" rows="4" required>{{ old('work_done', $i->work_done) }}</textarea></label>
<button class="btn">Terminer l’intervention</button></form>
<p class="muted">Les pièces ajoutées sont retirées du stock à la fin.</p></div>
<script>
var c=document.getElementById('sig'),x=c.getContext('2d'),on=false,drawn=false;
function init(){x.fillStyle='#fff';x.fillRect(0,0,c.width,c.height);x.lineWidth=3;x.lineCap='round';x.strokeStyle='#000';drawn=false}
function p(e){var r=c.getBoundingClientRect();return{x:(e.clientX-r.left)*c.width/r.width,y:(e.clientY-r.top)*c.height/r.height}}
c.addEventListener('pointerdown',function(e){on=true;drawn=true;document.getElementById('sigerr').style.display='none';var q=p(e);x.beginPath();x.moveTo(q.x,q.y);c.setPointerCapture(e.pointerId)});
c.addEventListener('pointermove',function(e){if(!on)return;var q=p(e);x.lineTo(q.x,q.y);x.stroke()});
['pointerup','pointercancel'].forEach(function(t){c.addEventListener(t,function(){on=false})});
document.getElementById('clear').addEventListener('click',function(){init();document.getElementById('sigerr').style.display='none'});
document.getElementById('signform').addEventListener('submit',function(e){if(!drawn){e.preventDefault();document.getElementById('sigerr').style.display='block';return}document.getElementById('sigdata').value=c.toDataURL('image/png')});
init();
</script>
@else
<div class="card"><p><strong>Intervention {{ $i->status === 'done' ? 'terminée' : 'annulée' }}.</strong></p>@if($i->signature_path)<p class="muted">Signée par {{ $i->signed_by }}.</p>@endif<a class="btn line" href="{{ route('tech.index') }}">Retour à mes interventions</a></div>
@endif
@endsection
