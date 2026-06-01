document.addEventListener('DOMContentLoaded', function () {
    setTimeout(function () {
        document.querySelectorAll('.alert-dismissible').forEach(function (alert) {
            let bsAlert = new bootstrap.Alert(alert);
            setTimeout(function () { bsAlert.close(); }, 5000);
        });
    }, 1000);
});
