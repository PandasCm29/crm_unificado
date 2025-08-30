window.addEventListener("load", () => {
    // MODAL PROTEGIDO
    const btnUpdate = document.getElementById('button-search');
    const modalPermiso = document.getElementById('modalPermiso') || null;
    const btnsCerrar = modalPermiso?.querySelectorAll('.btn-cerrar') || [];
    const modalEstado = document.getElementById('modalEstado');
    const btnsCerrarVM = modalEstado?.querySelectorAll('.btn-cerrar-vm') || [];

    function abrirModalProtegida() {
        modalPermiso.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    function cerrarModalProtegida() {
        // if(event) event.stopPropagation();
        modalPermiso?.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
    modalPermiso?.addEventListener("click", e => e.target === modalPermiso && cerrarModalProtegida());
    btnsCerrar.forEach(b => b.addEventListener('click', ()=>cerrarModalProtegida()));
    
    function eliminarCliente(idcliente) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: 'El cliente se podrá ver en Registros Eliminados',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
            // Llamada AJAX a tu controlador
            fetch(`${base}controller/cliente/notion/sync_to_notion.php`, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ idcliente })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Eliminado', data.message, 'success');
                    // Remover la fila de la tabla para reflejar el cambio
                    btnUpdate?.click();
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(() => {
                Swal.fire('Error', 'No se pudo conectar al servidor.', 'error');
            });
            }
        });
    }
    // Mostrar el modal de estado completo
    function abrirModalEstado(contenido) {
        const contenidoElemento = document.getElementById('estado-contenido');
        if (!modalEstado || !contenidoElemento) {
            console.error('Modal o elemento de contenido no encontrado');
            return;
        }
        contenidoElemento.textContent = contenido;
        modalEstado.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    // Cerrar el modal de estado
    function cerrarModalEstado(event) {
        if (event) event.stopPropagation();
        if (!modalEstado) return;
        modalEstado.classList.add('hidden');
        document.body.style.overflow = 'auto';
        }
    modalEstado?.addEventListener("click", e => e.target === modalEstado && cerrarModalEstado());
    btnsCerrarVM.forEach(b => b.addEventListener('click', ()=>cerrarModalEstado()));

    // ESTABLECER FUNCIONALIDAD A BOTONES DE ACCION
    window.setFunctions = function (filtered=false){
        // BUscar tr que no esten con none en su display
        const tabla = document.querySelector('.tabla-mini');
        if(!tabla) return;
        const tbody = tabla.querySelector('tbody');
        if(!tbody) return;
        if(filtered){
            const tr = tbody.querySelectorAll('tr') || [];
            tr.forEach(el => {
                if(el.style.display === 'none'){
                    el.remove();
                }
            });
        }
        const filas = document.querySelectorAll('.tabla-mini tbody tr')||[];
        filas.forEach(fila => {
            if(fila.style.display === 'none') return; // Si la fila está oculta, no hacer nada
            const cols = fila.querySelectorAll('td') || [];
            if(cols){
                const actionsTd = cols[0];
                const statusContainer = cols[9];
                const actionsContainer = actionsTd.querySelector('div');
                if(!actionsContainer) return;
                const id = actionsContainer.dataset.id;
                const btns = actionsContainer.querySelectorAll('button') || [];
                const spanStatus = statusContainer.querySelector('.ver-status');
                if(id && btns && spanStatus){
                    const propio = actionsContainer.dataset.propio == "si";
                    btns[0].addEventListener("click", ()=> window.abrirModalEditar(id));
                    btns[1].addEventListener("click", ()=> propio ? eliminarCliente(id) : abrirModalProtegida());
                    spanStatus.addEventListener("click", ()=>window.abrirModalEditar(id, 3));
                }
            }            
        });
    }
    setFunctions();
});