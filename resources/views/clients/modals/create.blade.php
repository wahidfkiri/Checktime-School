<div class="modal-header">
    <h5 class="modal-title">Nouvelle école</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<form id="create-client-form" action="{{ route('clients.store') }}" method="POST">
    @csrf
    <div class="modal-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="raison_sociale" class="form-label">Raison Sociale *</label>
                <input type="text" class="form-control" id="raison_sociale" name="raison_sociale" required>
                <div class="invalid-feedback"></div>
            </div>
            <div class="col-md-6 mb-3">
                <label for="sigle" class="form-label">Sigle</label>
                <input type="text" class="form-control" id="sigle" name="sigle">
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="rccm" class="form-label">RCCM *</label>
                <input type="text" class="form-control" id="rccm" name="rccm" required>
                <div class="invalid-feedback" id="rccm-feedback"></div>
            </div>
            <div class="col-md-6 mb-3">
                <label for="ifu" class="form-label">IFU</label>
                <input type="text" class="form-control" id="ifu" name="ifu">
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <div class="mb-3">
            <label for="directeur" class="form-label">Directeur</label>
            <input type="text" class="form-control" id="directeur" name="directeur">
            <div class="invalid-feedback"></div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="email" class="form-label">Email *</label>
                <input type="email" class="form-control" id="email" name="email" required>
                <div class="invalid-feedback"></div>
            </div>
            <div class="col-md-6 mb-3">
                <label for="telephone" class="form-label">Téléphone</label>
                <input type="text" class="form-control" id="telephone" name="telephone">
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <div class="mb-3">
            <label for="adresse" class="form-label">Adresse complète</label>
            <textarea class="form-control" id="adresse" name="adresse" rows="2"></textarea>
            <div class="invalid-feedback"></div>
        </div>

        <hr>
        <h5 class="mb-3">Accès au portail de l'école</h5>
        <div class="row">
            <div class="col-md-12 mb-3">
                <label for="login_user" class="form-label">Login (email de connexion) *</label>
                <input type="text" class="form-control" id="login_user" name="login_user" required>
                <div class="invalid-feedback"></div>
            </div>
            <div class="col-md-6 mb-3">
                <label for="password_user" class="form-label">Mot de passe *</label>
                <input type="password" class="form-control" id="password_user" name="password_user" required>
                <div class="invalid-feedback"></div>
            </div>
            <div class="col-md-6 mb-3">
                <label for="password_confirmation" class="form-label">Confirmation *</label>
                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                <div class="invalid-feedback"></div>
            </div>
        </div>

        <hr>
        <h5 class="mb-3">Accès à l'API CheckTime (biométrie)</h5>
        <div class="mb-3">
            <label for="general_token" class="form-label">Token général API *</label>
            <input type="text" class="form-control" id="general_token" name="general_token" required>
            <div class="invalid-feedback"></div>
        </div>

        <div class="mb-3 form-check d-none">
            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" checked>
            <label class="form-check-label" for="is_active">École active</label>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Enregistrer</button>
    </div>
</form>

<script>
$(document).ready(function() {
    // Générer un login suggéré à partir de la raison sociale
    $('#raison_sociale').on('blur', function() {
        if (!$('#login_user').val()) {
            var raison = $(this).val();
            if (raison) {
                var login = raison.toLowerCase()
                    .normalize('NFD').replace(/[̀-ͯ]/g, '')
                    .replace(/[^a-z0-9]/g, '_').replace(/_+/g, '_').replace(/^_|_$/g, '');
                $('#login_user').val(login.substring(0, 20));
            }
        }
    });

    // Validation mot de passe côté client
    $('#create-client-form').on('submit', function(e) {
        var isValid = true;
        var password = $('#password_user').val();
        var confirm = $('#password_confirmation').val();

        if (password.length < 8) {
            $('#password_user').addClass('is-invalid').next('.invalid-feedback').text('Au moins 8 caractères.');
            isValid = false;
        }
        if (password !== confirm) {
            $('#password_confirmation').addClass('is-invalid').next('.invalid-feedback').text('Les mots de passe ne correspondent pas.');
            isValid = false;
        }
        if (!isValid) { e.preventDefault(); e.stopImmediatePropagation(); }
    });
});
</script>
