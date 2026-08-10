document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.techi-repeatable').forEach(function (group) {
        var list = group.querySelector('.techi-repeatable-list');
        var addButton = group.querySelector('.techi-add-item');
        var type = group.dataset.type;

        addButton.addEventListener('click', function () {
            var row = document.createElement('div');
            row.className = 'techi-repeatable-row';
            row.innerHTML = '<input class="widefat" type="text" name="techi_' + type + '[]" value="" />' +
                '<button type="button" class="button-link-delete techi-remove-item">Remove</button>';
            list.appendChild(row);
        });

        group.addEventListener('click', function (event) {
            if (event.target.classList.contains('techi-remove-item')) {
                var rows = list.querySelectorAll('.techi-repeatable-row');
                if (rows.length > 1) {
                    event.target.closest('.techi-repeatable-row').remove();
                } else {
                    event.target.closest('.techi-repeatable-row').querySelector('input').value = '';
                }
            }
        });
    });
});
