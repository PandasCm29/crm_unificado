document.addEventListener("DOMContentLoaded", () => {
    //MODAL
    const modalCliente          = document.getElementById('modalCliente');

    const modalPermiso          = modalCliente.querySelector('#modalPermiso');



    //modal de protegida
    function cerrarModalProtegida(event) {
        if(event) event.stopPropagation();
        if (!modalPermiso) return;
        modalPermiso.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
})