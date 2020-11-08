import 'bootstrap';
import '@fortawesome/fontawesome-free';
import '../css/admin.css';
import $ from 'jquery';

// Fonction qui permet d'afficher le nom de l'image dans l'input -> admin-add-product

$('.custom-file-input').on('change', function (event) {
    var inputFile = event.currentTarget;
    $(inputFile).parent()
        .find('.custom-file-label')
        .html(inputFile.files[0].name);
});



