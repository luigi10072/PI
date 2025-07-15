// js/script.js

document.addEventListener('DOMContentLoaded', () => {
    const noteForm = document.querySelector('.note-form');

    if (noteForm) {
        noteForm.addEventListener('submit', (event) => {
            const titleInput = document.getElementById('note_title');
            const contentInput = document.getElementById('note_content');

            if (titleInput.value.trim() === '' || contentInput.value.trim() === '') {
                alert('Por favor, completa tanto el título como el contenido de la nota.');
                event.preventDefault(); // Evita el envío del formulario si está vacío
            }
        });
    }

    console.log('Sistema de Notas cargado. ¡Añade tus recordatorios!');
});