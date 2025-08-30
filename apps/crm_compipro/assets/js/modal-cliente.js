document.addEventListener("DOMContentLoaded", () => {
    const btnModalAgregar = document.getElementById("btnAgregarCliente");
    const btnUpdate = document.getElementById('button-search');
    //MODALES
    const modal          = document.getElementById('modalCliente');
    const tituloModal = modal?.querySelector('#modalTitulo');
    const btnCerrarModal = modal?.querySelector('#btnCerrarModal');
    // PESTAÑAS
    const tabBtns        = modal?.querySelectorAll('.tab-btn') || [];
    const tabBtnsData = [...tabBtns].map(el => el.dataset.tab);
    const [tab1Personal, tab2Trabajo, tab3Status, tab4Ante, tab5Perfil] = tabBtnsData;
    // FORM
    const form           = modal?.querySelector('#formCliente');
    const seccionAct     = form?.querySelector('#seccionActiva');
    const contentContainers    = form?.querySelectorAll('.tab-content');
    const [container1Personal, container2Trabajo, container3Status, container4Ante, container5Perfil] = contentContainers;
    // TABLA (Solo en Listar clientes)
    const tabla = document.querySelector('.tabla-mini');

    // PESTAÑA 1 (PERSONAL)
    const selectDistrito = container1Personal?.querySelector("select");
    const btnNext     = container1Personal?.querySelector('#btn-next');//
    const emailsContainer = container1Personal?.querySelector('#emailFields');
    const inputsNumbers = container1Personal?.querySelectorAll('input[type="tel"]')
    const btnAgregarCampoEmail = container1Personal?.querySelector('#agregar-campo-email');
    // PESTAÑA 2 (TRABAJO)
    const btnPrev     = document.getElementById('btn-prev');//
    const btnCancelStep = document.getElementById('btn-cancel-step');//
    const btnSave = document.getElementById("btn-submit");//
    const spinner = btnSave.querySelector('.spinner');
    // PESTAÑA 3 (HISTORIAL STATUS)
    const btnAgregarHStatus = document.getElementById('btnAgregarHistorial');
    const textAreaHStatus= container3Status?.querySelector('textarea');
    const listContainerHStatus= container3Status?.querySelector('#lista-historial-status');
    const btnCancelarEdicion = document.getElementById('btnCancelarEdicionHistorial');
    // PESTAÑA 4 (HISTORIAL ANTECEDENTES)
    const btnAgregarHAntecedentes = document.getElementById('btnAgregarAntecedente');
    const listContainerHAnte= container4Ante?.querySelector('#lista-historial-antecedentes');
    // PESTAÑA 5 (PERFIL)
    const btnPrevPerfil = container5Perfil?.querySelector('#btn-prev-perfil');//# ?????????????? 
    const camposMapping = {
        'TIPO DE CLIENTE': 'tipo_cliente',
        'POLÍTICA DE PAGO': 'politica_pago',
        'TRABAJA CON PROVEEDORES': 'trabaja_proveedores',
        'PROCEDIM. ESPECIAL EN FACTURACIÓN Y DESPACHO': 'procedimiento_facturacion',
        'FRECUENCIA DE COMPRA': 'frecuencia_compra',
        'ADICIONALES': 'adicionales'
    };

    /*function cerrarModalCliente() {
        modal.classList.toggle("hidden", true);
        document.body.style.overflow = "";
    }*/
   function cerrarModalCliente() {
        modal.classList.toggle("hidden", true);
        document.body.style.overflow = "";

        // Limpiar todos los campos del formulario
        form.reset();

        // Limpiar textarea de historial status
        textAreaHStatus.value = '';
        btnAgregarHStatus.textContent = 'Agregar al historial';
        delete btnAgregarHStatus.dataset.editingId;
        btnCancelarEdicion.classList.add('hidden');

        // Si tienes otros campos dinámicos, límpialos aquí también
        // Por ejemplo, emails, historial, etc.
    }
    //
    // INSTANCIAS
    //
    [btnCerrarModal, btnCancelStep].forEach(b => b?.addEventListener('click', cerrarModalCliente));

    function enfocarPrimerCampoVacio(container, nombresCampos) {
        for (const nombre of nombresCampos) {
            const campo = container.querySelector(`[name="${nombre}"]`);
            if (campo && campo.offsetParent !== null) { // offsetParent !== null => visible
                const valor = campo.type === 'checkbox' || campo.type === 'radio'
                    ? campo.checked
                    : campo.value?.trim();

                if (!valor) {
                    campo.focus();
                    return;
                }
            }
        }
    }

    // función para mostrar solo la sección activa
    function switchTo(tabName) {
        if(seccionAct.value === tabName) return; // Si ya está en la pestaña, no hacer nada
        seccionAct.value = tabName;
        tabBtns.forEach(btn => {
            const active = btn.dataset.tab === tabName;
            btn.classList.toggle('font-semibold', active);
            btn.classList.toggle('border-l', active);
            btn.classList.toggle('border-t', active);
            btn.classList.toggle('border-r', active);
            btn.classList.toggle('rounded-t', active);
            btn.classList.toggle('text-gray-600', !active);
        });
        contentContainers.forEach(tc =>tc.classList.toggle('hidden', tc.id !== `tab-${tabName}`));
        // RECORRER LOS CAMPOS PARA ESTABLECER FOCUS CON LA FUNCION 'enfocarPrimerCampoVacio'
        const focusNames = [['nombres', 'apellidos', 'email-1'],['razon'], ['nuevo_status'],
                            ['nuevo_antecedente'], Object.values(camposMapping)];        
        const index = tabBtnsData.indexOf(tabName);
        const container = contentContainers[index];
        const campos = focusNames[index];
        enfocarPrimerCampoVacio(container, campos);
    }
    tabBtns?.forEach(btn => {
        btn.addEventListener('click', () => {
            const t = btn.dataset.tab;
            // if (form.dataset.mode === 'create' && ![tab1Personal, tab2Trabajo, tab3Status, tab4Ante].includes(t)
            // ) return;
            // form.action = `controller/cliente.php?action=${form.dataset.mode}`;
            if(tab5Perfil == t){
                form.dataset.mode='perfil'
                // form.action = 'controller/cliente.php?action=perfil';
            }
            switchTo(t);
        });
    });
    btnPrev.addEventListener('click', () => switchTo(tab1Personal));    // Paso ← Personal
    btnPrevPerfil.addEventListener('click', () => switchTo(tab2Trabajo));    // Paso ← Personal
    btnNext.addEventListener('click', () => switchTo(tab2Trabajo));    // Paso → Trabajo
    //
    // (PERSONAL)
    const regexTelefono = /^\+\d{6,15}$/;
    function validacionNumeros(event){
        const input = event.target;
        const mensaje = input.nextElementSibling;
        const valor = input.value.trim();

        // Limpia las clases de color previas
        input.classList.remove("border-green-500", "border-red-500", "focus:ring-green-500", "focus:ring-red-500");
        input.classList.add("border-gray-300", "focus:ring-primary");
        
        if (valor === "") {
            mensaje.textContent = "Formato: +[código de país][número]";
            mensaje.classList.remove("text-green-600", "text-red-600");
            mensaje.classList.add("text-gray-500");
            return;
        }

        input.classList.remove("border-gray-300");
        if (regexTelefono.test(valor)) {
            input.classList.add("border-green-500",  "focus:ring-green-500");
            mensaje.textContent = "✅ Número válido";
            mensaje.classList.remove("text-gray-500", "text-red-600");
            mensaje.classList.add("text-green-600");
        } else {
            input.classList.add("border-red-500", "focus:ring-red-500");
            mensaje.textContent = "❌ Formato inválido. Ej: +51912345678";
            mensaje.classList.remove("text-gray-500", "text-green-600");
            mensaje.classList.add("text-red-600");
        }
    }
    inputsNumbers.forEach(input=>{
        input.addEventListener('input', validacionNumeros)
    })
    function vaciarFormularioCliente() {
        cleanEmailsContainer();
        inputsNumbers?.forEach(input => {
            input.classList.remove("border-green-500", "border-red-500", "focus:ring-green-500", "focus:ring-red-500");
            const messageElement = input.nextElementSibling;
            if(messageElement) {messageElement.textContent = "";} // Reset to empty message
        });
        Array.from(form.elements).forEach(el => {
            if (['INPUT','TEXTAREA','SELECT'].includes(el.tagName) && !['button','submit','reset'].includes(el.type)) {
                if(el.type === 'checkbox') {
                    el.checked = false;
                }
                if(el.name !== 'fatencion' && el.name !== 'actual') {
                    el.value = (el.name === 'idcliente') ? 'AUTO' : '';
                }else{
                    const fechaHoy = new Date();
                    const year = fechaHoy.getFullYear();
                    const month = String(fechaHoy.getMonth() + 1).padStart(2, '0'); // Los meses van de 0 a 11
                    const day = String(fechaHoy.getDate()).padStart(2, '0');
                    const fechaFormateada = `${day}-${month}-${year}`;
                    el.value = fechaFormateada;
                }
            }
        });
    }
    // Abrir modal para AGREGAR Cliente
    window.abrirModalAgregar=function(clienteData=null){
        form.dataset.mode = 'create';
        // tabC5.querySelectorAll('textarea[required]')?.forEach(i => i.removeAttribute('required'));
        vaciarFormularioCliente();
        // CAMBIAR TITULO A AGREGAR CLIENTE
        tituloModal.innerHTML = '<i class="fas fa-user-plus text-xl mr-5"></i>Agregar Cliente';
        // — Mostrar solo personal, trabajo, historial-status y antecedentes
        tabBtns.forEach(btn => {
            btn.parentElement.classList.toggle(
                'hidden',
                ![tab1Personal, tab2Trabajo, tab3Status, tab4Ante].includes(btn.dataset.tab)
            );
        });
        // MOSTRAMOS EL CONTENIDO VACIO EN Historial de Status y Antecedentes
        [ container3Status, container4Ante ].forEach(tab => {
            const divs = tab.querySelectorAll('div');
            divs.forEach(div => div.classList.toggle('hidden', true));
            const existingPlaceholder = tab.querySelector('.placeholder-create');
            // Si no existe aún, añadir placeholder
            if (!existingPlaceholder) {
                const nombreTab = tab.id.replace("tab-", "").replace("-", " de ");
                const ph = document.createElement('div');
                ph.className = 'p-4 bg-gray-100 rounded text-center placeholder-create';
                ph.innerHTML = `<p class="text-gray-700">Contenido de ${nombreTab}</p>`;
                tab.appendChild(ph);
            }else{
                existingPlaceholder.classList.toggle("hidden", false);
            }
        });
        if (clienteData) {
            llenarFormulario(clienteData);
        }
        // Arrancamos en PERSONAL
        switchTo(tab1Personal);
        // MOSTRAR MODAL
        modal.classList.remove("hidden");
        document.body.style.overflow = "hidden";
    }
    btnModalAgregar?.addEventListener("click", window.abrirModalAgregar );
    // Funcionalidades para mas Email
    function cleanEmailsContainer(){
        const containers = emailsContainer?.querySelectorAll('div.flex.items-center.gap-2') || [];
        // Convertir a array y eliminar todos excepto el primero
        if (containers.length > 1) {
            containers.forEach((el, index) => {
                if (index > 0) el.remove();
            });
        }
    }
    let totalEmails = 1;
    function addEmailsContainers (values, clean =false ){
        function orderEmailNames(){
            const emailsInput = emailsContainer?.querySelectorAll('input');
            emailsInput.forEach((input, index) => input.name=`email-${index+1}`);
            totalEmails=emailsInput.length;
        }
        // Si se pidió limpiar contenedor
        if (clean) cleanEmailsContainer();
        const original = container1Personal.querySelector('input[type=email]');
        // Si hay al menos un valor no vacío
        if (values.length > 0) {
            // Establecer el primer valor en el input original
            original.value = values[0];

            // Agregar el resto
            values.slice(1).forEach(value => {
                const clonInput = original.cloneNode(true);
                clonInput.value = value;

                const container = document.createElement('div');
                container.className = original.parentElement.className;

                const btnEliminar = document.createElement('button');
                btnEliminar.type = "button";
                btnEliminar.title = "Eliminar este campo";
                btnEliminar.className = "mr-1.5 p-1 bg-red-100 rounded-full text-red-600 hover:bg-red-200 transition-transform hover:scale-110";
                btnEliminar.onclick = () => container.remove() || orderEmailNames();

                const icon = document.createElementNS("http://www.w3.org/2000/svg", "svg");
                icon.setAttribute("xmlns", "http://www.w3.org/2000/svg");
                icon.setAttribute("viewBox", "0 0 24 24");
                icon.setAttribute("fill", "none");
                icon.setAttribute("stroke", "currentColor");
                icon.setAttribute("class", "w-3.5 h-3.5 m-0");
                icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>`;

                btnEliminar.appendChild(icon);
                container.appendChild(clonInput);
                container.appendChild(btnEliminar);
                emailsContainer?.appendChild(container);
            });
        } else {
            addEmailsContainers([original.value, '']);
        }
        orderEmailNames();
    }
    btnAgregarCampoEmail?.addEventListener('click', addEmailsContainers);
    
    function llenarFormulario(data) {
        Object.entries(data).forEach(([key, value]) => {
            // Excepción para el campo "email" si viene como JSON
            if (key === "email") {
                let lista = [''];
                if (value && value.trim() !== '') {                    
                    try {
                        lista = value.split(', ').map(email => email.trim());
                    } catch (e) {
                        console.error("JSON de 'email' inválido:", e);
                    }
                }
                addEmailsContainers(lista, true);
                return;
            }

            const campos = form.querySelectorAll(`[name="${key}"]`);
            if (!campos.length) return;

            campos.forEach(campo => {
                const tag = campo.tagName;
                const type = campo.type;

                if (type === 'checkbox') {
                    campo.checked = (value === '1' || value === 1 || value === true);
                } else if (type === 'radio') {
                    campo.checked = (campo.value == value);
                } else if (tag === 'SELECT') {
                    campo.value = value;
                } else if (campo.classList.contains('datepicker') && campo._flatpickr) {
                    campo._flatpickr.setDate(value, true);
                }  else {
                    campo.value = value;
                }
            });
            const dateActual = form.querySelector('[name="actual"]');
            if (dateActual) {
                const fechaHoy = new Date();
                const year = fechaHoy.getFullYear();
                const month = String(fechaHoy.getMonth() + 1).padStart(2, '0'); // Los meses van de 0 a 11
                const day = String(fechaHoy.getDate()).padStart(2, '0');
                const fechaFormateada = `${day}-${month}-${year}`;
                dateActual.value = fechaFormateada;
            }
        });
    }

    function limpiarCamposPerfil() {
        //LIMPIAR TODOS LOS CAMPOS TEXTAREA EN LA PESTAÑA DE PERFIL
        const camposPerfil = container5Perfil.querySelectorAll('textarea');
        camposPerfil.forEach(campo => campo.value = '');
    }
    function llenarHistorial(data, tab){
        const indexContainer = tabBtnsData.indexOf(tab);
        const container = contentContainers[indexContainer];
        const divs = container.querySelectorAll('div') || [];
        divs.forEach(div => div.classList.toggle('hidden', false));
        const listContainer = container.querySelector(`#lista-${tab}`);
        listContainer.innerHTML = '';
        const existingPlaceholder = container.querySelector('.placeholder-create');
        existingPlaceholder?.classList.toggle('hidden', true);
        if (Array.isArray(data) && data.length > 0) {
            data.forEach(item => {
                const block = document.createElement('div');
                block.className = "bg-white p-4 border-l-4 border-orange-500 rounded-md shadow-sm";
                block.innerHTML = `
                    <div class="flex justify-between items-center mb-1">
                        <div class="flex items-center space-x-2">
                            <p class="text-sm font-semibold text-gray-800">${item.usuario}</p>
                            <button type="button" class="btn-edit-hist text-xs text-blue-600">✏️</button>
                        </div>
                        <span class="text-xs text-gray-500">${item.fecha}</span>
                    </div>
                    <p class="text-sm text-gray-600 whitespace-pre-line">${item.descripcion}</p>
                `;
                block.dataset.idstatus = item.id;
                listContainer.appendChild(block);
            });
        } else {
            listContainer.innerHTML =
            `<p class="text-sm text-gray-500 italic">No hay historial registrado.</p>`;
        }

    }
    function llenarPerfil(data){
        let perfilData = {};
        if (data && data.trim() !== '') {
            try{
                perfilData = JSON.parse(data);

            }catch{
                perfilData={}
            }
        }
        const divs = container5Perfil.querySelectorAll('div') || [];
        const divForm = divs[1];
        divForm.innerHTML ='';
        for (const [titulo, name] of Object.entries(camposMapping)) {
            const valor = perfilData[titulo] ?? '';
            const divCampo = document.createElement('div');

            const label = document.createElement('p');
            label.className = 'text-xs font-medium text-red-700 mb-1';
            label.innerHTML = `${titulo}<span class="text-red-500">*</span>`;

            const textarea = document.createElement('textarea');
            textarea.name = name;
            textarea.rows = 3;
            textarea.className = 'w-full text-sm border border-gray-300 rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-primary';
            // textarea.required = true;
            textarea.placeholder = 'Escribe aquí...';
            textarea.value = valor;

            divCampo.appendChild(label);
            divCampo.appendChild(textarea);
            divForm.appendChild(divCampo);
        }
    }
    // Abrir modal para EDITAR Cliente
    window.abrirModalEditar = function(clienteId, tabContent=1) {
        form.dataset.mode = 'edit';
        // CAMBIAR A ACCION EDITAR AL FORM
        fetch(base+'controller/cliente/obtener.php?id=' + clienteId)
            .then(res => res.json())
            .then(cliente => {
                // Mostrar TODAS las pestañas, incluye botón Perfil
                tabBtns.forEach(btn => btn.parentElement.classList.toggle('hidden', false));
                // Ir a pestaña indicada por tabContent
                const idx = Math.max(0, tabContent - 1);
                if (tabBtns[idx]) tabBtns[idx].click();

                tituloModal.innerHTML = '<i class="fas fa-user-edit text-xl mr-5"></i>Editar Cliente';
                // LLENAR PERSONAL y TRABAJO
                llenarFormulario(cliente);
                // LLENAR HISTORIAL DE STATUS
                llenarHistorial(cliente.historial, tab3Status);
                // LLENAR HISTORIAL DE ANTECEDENTES
                llenarHistorial(cliente.antecedentes, tab4Ante);
                // LLENAR PERFIL
                llenarPerfil(cliente.perfil); // cliente.perfil
                modal.classList.remove('hidden');
                document.body.style.overflow = "hidden";
            });
    }
    // Funcionalidad de Distro (Personal)
    // const otroDistrito   = document.getElementById("otroDistrito");
    // selectDistrito.addEventListener("change", function () {
    //     this.value === "otro"
    //     ? otroDistrito.classList.remove("hidden")
    //     : otroDistrito.classList.add("hidden");
    // });
    //
    // (TRABAJO)
    function prepararCheckboxes() {
        // Recorremos todos los checkbox del formulario
        container2Trabajo?.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            if (!checkbox.checked) {
                // Si no está marcado, agregamos un input hidden con valor 0
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = checkbox.name;
                hidden.value = '0';
                form.appendChild(hidden);
            } else {
                // Si está marcado, aseguramos que tenga value="1"
                checkbox.value = '1';
            }
        });
    }
    // 
    // (HISTORIAL STATUS)
    btnCancelarEdicion?.addEventListener('click', function() {
      btnAgregarHStatus.textContent = 'Agregar al historial';
      delete btnAgregarHStatus.dataset.editingId;
      textAreaHStatus.value = '';
      btnCancelarEdicion.classList.add('hidden');
    });

    // Evento delegado para editar comentarios del historial
    listContainerHStatus.addEventListener('click', function(e) {
      const div = e.target.closest('div[data-idstatus]');
      if (!div) return;
      const idstatus = +div.dataset.idstatus;
      if (e.target.classList.contains('btn-edit-hist')) {
        textAreaHStatus.value = div.querySelector('p.text-sm.text-gray-600').textContent;
        btnAgregarHStatus.textContent = 'Editar Comentario';
        btnAgregarHStatus.dataset.editingId = idstatus;
        btnCancelarEdicion?.classList.remove('hidden');
      }
    });

    btnAgregarHStatus?.addEventListener('click', async () => {
        const idcliente = document.querySelector('input[name="idcliente"]').value;
        const texto = textAreaHStatus.value.trim();
        const editingId = btnAgregarHStatus.dataset.editingId;

        if (!texto) {
            Swal.fire({
                icon: 'warning',
                title: 'Campo vacío',
                text: 'Por favor, escribe un status.',
                confirmButtonText: 'Aceptar',
                confirmButtonColor: '#f44336'
            });
            return;
        }
    
        if (!idcliente || idcliente === 'AUTO') {
            Swal.fire({
                icon: 'warning',
                title: 'Error',
                text: 'ID de cliente inválido.',
                confirmButtonText: 'Aceptar',
                confirmButtonColor: '#f44336'
            });
            return;
        }
        let method = 'POST';
        let payload = {
            tabla:   'clientes',
            idtabla: Number(idcliente),
            status:  texto
        };
        if (editingId) {
            method = 'PUT';
            payload.idstatus = Number(editingId);
        }
        fetch(base+'controller/historial/crear.php', {
            method,
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify(payload)
        }).then(res => res.json()).then(data => {
            if (data.success) {
                // Actualiza el comentario en la lista si fue edición
                if (method === 'PUT') {
                    const div = listContainerHStatus.querySelector(`div[data-idstatus="${editingId}"]`);
                    if (div) {
                        // Actualiza el texto del comentario
                        div.querySelector('p.text-sm.text-gray-600').textContent = texto;
                        // Actualiza la fecha mostrada
                        const fecha = data.data?.fecha || new Date().toLocaleString();
                        div.querySelector('span.text-xs.text-gray-500').textContent = fecha;
                        // Actualiza el nombre de usuario mostrado
                        const usuario = data.data?.usuario || 'Tú';
                        const pUsuario = div.querySelector('p.text-sm.font-semibold.text-gray-800');
                        if (pUsuario) pUsuario.textContent = usuario;
                    }
                    btnAgregarHStatus.textContent = 'Agregar al historial';
                    delete btnAgregarHStatus.dataset.editingId;
                    textAreaHStatus.value = '';
                    btnCancelarEdicion.classList.add('hidden');
                    Swal.fire({ icon:'success', title:'¡Actualizado!', text:data.message})
                    .then(() => {
                        function esperarYRecargarTabla() {
                            if (typeof window.reloadClientesTable === "function") {
                                window.reloadClientesTable();
                            } else {
                                setTimeout(esperarYRecargarTabla, 100);
                            }
                        }
                        esperarYRecargarTabla();
                    });
                } else {
                    // Eliminar Contenedor con texto 'No hay historial registrado'
                    listContainerHStatus?.querySelector('p.text-sm.text-gray-500.italic')?.remove();
                    const fecha = data.data?.fecha || new Date().toLocaleString();
                    const usuario = 'Tú';
                    const descripcion = data.data?.descripcion || texto;
                    const nuevo = document.createElement('div');
                    nuevo.className = "bg-white p-4 border-l-4 border-orange-500 rounded-md shadow-sm";
                    nuevo.innerHTML = `
                        <div class="flex justify-between items-center mb-1">
                            <div class="flex items-center space-x-2">
                                <p class="text-sm font-semibold text-gray-800">${usuario}</p>
                                <button type="button" class="btn-edit-hist text-xs text-blue-600">✏️</button>
                            </div>
                            <span class="text-xs text-gray-500">${fecha}</span>
                        </div>
                        <p class="text-sm text-gray-600 whitespace-pre-line">${descripcion}</p>
                    `;
                    nuevo.dataset.idstatus = data.data?.idstatus;
                    listContainerHStatus?.prepend(nuevo);
                    textAreaHStatus.value = '';
                    Swal.fire({ icon:'success', title:'¡Guardado!', text:data.message})
                    .then(() => {
                        function esperarYRecargarTabla() {
                            if (typeof window.reloadClientesTable === "function") {
                                window.reloadClientesTable();
                            } else {
                                setTimeout(esperarYRecargarTabla, 100);
                            }
                        }
                        esperarYRecargarTabla();
                    });
                }
            } else {
                Swal.fire({ icon:'error', title:'Error al guardar', text:data.message });
            }
        });
    });
    
    // Función para cargar los antecedentes
    function cargarAntecedentes(idcliente) {
        const listaAntecedentes = document.getElementById('lista-historial-antecedentes');
        listaAntecedentes.innerHTML = '<p class="text-sm text-gray-500">Cargando...</p>';
    
        fetch(base+`controller/antecedentes/listar_antecedentes.php?idcliente=${idcliente}`)
            .then(res => res.json())
            .then(data => {
                listaAntecedentes.innerHTML = '';
                if (data.success && data.data.length > 0) {
                    data.data.sort((a, b) => new Date(b.fecha) - new Date(a.fecha));
                    data.data.forEach(antecedente => {
                        const nuevo = document.createElement('div');
                        nuevo.className = "bg-white p-4 border-l-4 border-orange-500 rounded-md shadow-sm";
                        nuevo.innerHTML = `
                            <div class="flex justify-between items-center mb-1">
                            <p class="text-sm font-semibold text-gray-800">${antecedente.usuario}</p>
                            <span class="text-xs text-gray-500">${antecedente.fecha}</span>
                            </div>
                            <p class="text-sm text-gray-600 whitespace-pre-line">${antecedente.descripcion}</p>
                        `;
                        listaAntecedentes.appendChild(nuevo); 
                    });
                } else {
                    listaAntecedentes.innerHTML = '<p class="text-sm text-gray-500">No hay antecedentes registrados.</p>';
                }
            })
            .catch(error => {
                console.error('Error al cargar antecedentes:', error);
                listaAntecedentes.innerHTML = '<p class="text-sm text-red-500">Error al cargar los antecedentes.</p>';
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo cargar los antecedentes.',
                    confirmButtonText: 'Aceptar',
                    confirmButtonColor: '#f44336'
                });
            });
    }
    
    // Agregar antecedente
    btnAgregarHAntecedentes?.addEventListener('click', async () => {
        const idcliente = document.querySelector('#tab-historial-antecedentes input[name="idcliente"]').value;
        const texto = document.querySelector('#tab-historial-antecedentes textarea').value.trim();

        if (!texto) {
            Swal.fire({
                icon: 'warning',
                title: 'Campo vacío',
                text: 'Por favor, escribe un antecedente.',
                confirmButtonText: 'Aceptar',
                confirmButtonColor: '#f44336'
            });
            return;
        }
    
        if (!idcliente || idcliente === 'AUTO') {
            Swal.fire({
                icon: 'warning',
                title: 'Error',
                text: 'ID de cliente inválido.',
                confirmButtonText: 'Aceptar',
                confirmButtonColor: '#f44336'
            });
            return;
        }
        fetch(base+'controller/antecedentes/crear_antecedente.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ status: texto, idcliente })
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Eliminar Contenedor con texto 'No hay historial registrado'
                    listContainerHAnte?.querySelector('p.text-sm.text-gray-500.italic')?.remove();

                    document.querySelector('#tab-historial-antecedentes textarea').value = '';
                    const fecha = data.data?.fecha || new Date().toLocaleString();
                    const usuario = 'Tú';
                    const descripcion = data.data?.descripcion || texto;
                    const nuevo = document.createElement('div');
                    nuevo.className = "bg-white p-4 border-l-4 border-orange-500 rounded-md shadow-sm";
                    nuevo.innerHTML = `
                        <div class="flex justify-between items-center mb-1">
                        <p class="text-sm font-semibold text-gray-800">${usuario}</p>
                        <span class="text-xs text-gray-500">${fecha}</span>
                        </div>
                        <p class="text-sm text-gray-600 whitespace-pre-line">${descripcion}</p>
                    `;
                    listContainerHAnte?.prepend(nuevo);
                    Swal.fire({ icon: 'success', title: '¡Guardado!', text: data.message})
                    .then(() => {
                        function esperarYRecargarTabla() {
                        if (typeof window.reloadClientesTable === "function") {
                            window.reloadClientesTable();
                        } else {
                            setTimeout(esperarYRecargarTabla, 100);
                        }
                        }
                        esperarYRecargarTabla();
            
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error al guardar', text: data.message });
                }
            })
            .catch(error => {
                console.error('Error al agregar antecedente:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo agregar el antecedente.',
                    confirmButtonText: 'Aceptar',
                    confirmButtonColor: '#f44336'
                });
            });
    });
    //
    // Guardar información de Cliente (Personal y Trabajo)
    form.addEventListener('submit', async e => {
        e.preventDefault();
        const dataset = form.dataset.mode;
        // Al hacer clic en el botón Guardar Cliente y Guardar Perfil
        if(["edit", "create"].includes(dataset) && !form.checkValidity()){
            const labelsEmails = Array.from({ length: totalEmails }, (_, i) => `email-${i + 1}`);
            const camposPersonales = [...labelsEmails, 'nombres', 'apellidos'];
            const invalidElement = form.querySelector(':invalid');
            
            if (invalidElement) {
                // Buscar la pestaña contenedora (ajustar al selector real de tus pestañas)
                const tabContent = invalidElement.closest('.tab-content');
                const index = tabBtnsData.indexOf(tabContent.id.replace('tab-', ''));
                switchTo( tabBtnsData[index] );
                // Enfocar el elemento inválido para mostrar el mensaje
                invalidElement.focus();
                const campo = camposPersonales.includes(invalidElement.name) ? "que tenga *" : `"${invalidElement.name}"`;
                const text = `Por favor, completa correctamente el campo ${campo}`;
                Swal.fire({
                    icon: 'warning',
                    title: 'Rellena los campos obligatorios',
                    html: `<p> ${text}</p>`,
                    confirmButtonText: 'Aceptar',
                    confirmButtonColor: '#f44336',
                    width: '23rem', // Ancho actual
                    padding: '0.3rem',
                    customClass: {
                        icon: 'swal-icon-small',
                        title: 'swal-title-small'
                    }
                });
                // Aplicar estilos directamente
                document.querySelector('.swal-icon-small').style.fontSize = '1.2rem'; // Icono más pequeño
                document.querySelector('.swal-title-small').style.fontSize = '1.1rem';
                return;
            }
        }
        try {
            prepararCheckboxes();
            const data = new FormData(form);
            // Construir la URL correcta            
            const actionType = form.dataset.mode;
            const action = base+'controller/cliente.php?action=' + actionType;

            // const action = `${base}controller/cliente/notion/sync_to_notion.php?action=${actionType}`;
            if(btnSave) btnSave.disabled = true;
            if(btnCancelStep) btnCancelStep.disabled = true;
            spinner.classList.toggle('hidden', false);
            const resp = await fetch(action, { method: 'POST', body: data });
            const text = await resp.text();
            // Verificar si la respuesta parece ser HTML
            if (text.trim().startsWith('<!DOCTYPE') || text.includes('<html') || text.includes('<body')) {
                // Parsear el HTML para extraer el mensaje de error
                const parser = new DOMParser();
                const doc = parser.parseFromString(text, 'text/html');
                // Buscar un elemento que contenga el mensaje de error (ajusta el selector según tu HTML)
                const errorMessage = doc.querySelector('p')?.innerHTML.trim() || 'Error desconocido en la respuesta del servidor';

                Swal.fire({ 
                    icon: 'error', 
                    title: 'Error en el servidor', 
                    html: `<style>code.url { background-color: #eeeeee; font-family: monospace; padding: 0 2px; }</style>${errorMessage}`
                });
                return; // Salir para no intentar parsear HTML como JSON
            }

            const json = JSON.parse(text);
            if (json.success) {
                Swal.fire({ icon:'success', title:'¡Guardado!', text: json.message })
                .then(() => {
                    cerrarModalCliente();
                    function esperarYRecargarTabla() {
                        if (typeof window.reloadClientesTable === "function") {
                            window.reloadClientesTable();
                        } else {
                            setTimeout(esperarYRecargarTabla, 100);
                        }
                    }
                    esperarYRecargarTabla();
        
                });
            } else {
                Swal.fire({ icon:'error', title:'Error al guardar', text:json.message });
            }
        } catch (err) {
            Swal.fire({ icon:'error', title:'Error de red', text:err.message });
        }finally{
            if(btnSave) btnSave.disabled = false;
            if(btnCancelStep) btnCancelStep.disabled = false;
            spinner.classList.toggle('hidden', true);
        }
    });
    //FLATEPICKR
    flatpickr(".datepicker", {
        // altInput: true,
        altFormat: "d/m/Y",
        dateFormat: "Y-m-d",
        allowInput: true,
        locale: {
            firstDayOfWeek: 1,
            weekdays: {
            shorthand: ["Do","Lu","Ma","Mi","Ju","Vi","Sa"],
            longhand:  ["Domingo","Lunes","Martes","Miércoles","Jueves","Viernes","Sábado"]
            },
            months: {
            shorthand: ["Ene","Feb","Mar","Abr","May","Jun","Jul","Ago","Sep","Oct","Nov","Dic"],
            longhand:  ["Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"]
            }
        }
    });
});