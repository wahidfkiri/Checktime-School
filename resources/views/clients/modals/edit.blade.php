<div class="modal-header">
    <h5 class="modal-title">Modifier l'école</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<form id="edit-client-form" data-client-id="{{ $client->id }}">
    @csrf
    @method('PUT')
    <div class="modal-body">
        <ul class="nav nav-tabs mb-3" id="clientEditTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info-tab-pane" type="button" role="tab">
                    <i class="bi bi-info-circle me-1"></i> Infos École
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="access-tab" data-bs-toggle="tab" data-bs-target="#access-tab-pane" type="button" role="tab">
                    <i class="bi bi-key me-1"></i> Accès Portail
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="api-tab" data-bs-toggle="tab" data-bs-target="#api-tab-pane" type="button" role="tab">
                    <i class="bi bi-shield-lock me-1"></i> Token API
                </button>
            </li>
        </ul>

        <div class="tab-content" id="clientEditTabsContent">
            <!-- Infos École -->
            <div class="tab-pane fade show active" id="info-tab-pane" role="tabpanel" tabindex="0">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="raison_sociale" class="form-label">Raison Sociale *</label>
                        <input type="text" class="form-control" id="raison_sociale" name="raison_sociale" value="{{ old('raison_sociale', $client->raison_sociale) }}" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="sigle" class="form-label">Sigle</label>
                        <input type="text" class="form-control" id="sigle" name="sigle" value="{{ old('sigle', $client->sigle) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="rccm" class="form-label">RCCM *</label>
                        <input type="text" class="form-control" id="rccm" name="rccm" value="{{ old('rccm', $client->rccm) }}" data-client-id="{{ $client->id }}" required>
                        <div class="invalid-feedback" id="rccm-feedback"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="ifu" class="form-label">IFU</label>
                        <input type="text" class="form-control" id="ifu" name="ifu" value="{{ old('ifu', $client->ifu) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="directeur" class="form-label">Directeur</label>
                    <input type="text" class="form-control" id="directeur" name="directeur" value="{{ old('directeur', $client->directeur) }}">
                    <div class="invalid-feedback"></div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email *</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $client->email) }}" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="telephone" class="form-label">Téléphone</label>
                        <input type="text" class="form-control" id="telephone" name="telephone" value="{{ old('telephone', $client->telephone) }}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="adresse" class="form-label">Adresse complète</label>
                    <textarea class="form-control" id="adresse" name="adresse" rows="2">{{ old('adresse', $client->adresse) }}</textarea>
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <!-- Accès Portail -->
            <div class="tab-pane fade" id="access-tab-pane" role="tabpanel" tabindex="0">
                <h5 class="mb-3">Accès au portail de l'école</h5>
                <div class="mb-3">
                    <label for="login_user" class="form-label">Login (email de connexion) *</label>
                    <input type="text" class="form-control" id="login_user" name="login_user" value="{{ old('login_user', $client->user->email ?? '') }}" required>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password_user" class="form-label">Nouveau mot de passe</label>
                        <input type="password" class="form-control" id="password_user" name="password_user">
                        <div class="invalid-feedback"></div>
                        <small class="form-text text-muted">Laissez vide pour ne pas modifier.</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="password_confirmation" class="form-label">Confirmation</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="alert alert-info mt-2">
                    <i class="bi bi-info-circle me-2"></i>
                    <small>Le login est l'email de connexion de l'école au portail. Il doit être unique.</small>
                </div>
            </div>

            <!-- Token API -->
            <div class="tab-pane fade" id="api-tab-pane" role="tabpanel" tabindex="0">
                <h5 class="mb-3">Accès à l'API CheckTime (biométrie)</h5>
                <div class="mb-3">
                    <label for="general_token" class="form-label">Token général API</label>
                    <input type="text" class="form-control" id="general_token" name="general_token" value="{{ old('general_token', $client->accessConfigs->first()->general_token ?? '') }}">
                    <div class="invalid-feedback"></div>
                    <small class="form-text text-muted">Token utilisé pour la synchronisation biométrique.</small>
                </div>
            </div>
        </div>

        <div class="mb-3 form-check d-none">
            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ old('is_active', $client->is_active) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">École active</label>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Mettre à jour</button>
    </div>
</form>

<script>
$(document).ready(function() {
    // Vérification RCCM en temps réel
    $('#rccm').on('blur', function() {
        var rccm = $(this).val();
        var clientId = $(this).data('client-id');
        if (rccm && rccm.length > 0) {
            $.ajax({
                url: "{{ route('clients.check-rccm') }}",
                method: 'GET',
                data: { rccm: rccm, client_id: clientId || '' },
                success: function(response) {
                    var input = $('#rccm');
                    var feedback = $('#rccm-feedback');
                    if (response.exists) {
                        input.addClass('is-invalid');
                        feedback.text(response.message).addClass('text-danger');
                    } else {
                        input.removeClass('is-invalid');
                        feedback.text('RCCM disponible').removeClass('text-danger').addClass('text-success');
                        setTimeout(function() { feedback.text(''); }, 3000);
                    }
                }
            });
        }
    });

    // Effacer l'état d'erreur à la modification
    $('#edit-client-form input, #edit-client-form textarea').on('input', function() {
        $(this).removeClass('is-invalid');
    });
});
</script>
