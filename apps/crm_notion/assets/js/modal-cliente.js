document.addEventListener("DOMContentLoaded", () => {
  const btnModalAgregar = document.getElementById("btnAgregarCliente");
  const btnUpdate = document.getElementById("button-search");
  //MODALES
  const modal = document.getElementById("modalCliente");
  const tituloModal = modal?.querySelector("#modalTitulo");
  const btnCerrarModal = modal?.querySelector("#btnCerrarModal");
  // PESTAÑAS
  const tabBtns = modal?.querySelectorAll(".tab-btn") || [];
  const tabBtnsData = [...tabBtns].map((el) => el.dataset.tab);
  const [tab1Personal, tab2Trabajo, tab3Status, tab4Ante] = tabBtnsData;
  // FORM
  const form = modal?.querySelector("#formCliente");
  const seccionAct = form?.querySelector("#seccionActiva");
  const contentContainers = form?.querySelectorAll(".tab-content");
  const [
    container1Personal,
    container2Trabajo,
    container3Status,
    container4Ante,
    container5Perfil,
  ] = contentContainers;
  // TABLA (Solo en Listar clientes)
  const tabla = document.querySelector(".tabla-mini");

  // PESTAÑA 1 (PERSONAL)
  const selectDistrito = container1Personal?.querySelector("select");
  const btnUpdate1 = container1Personal?.querySelector("#btn-update-1"); //
  const emailsContainer = container1Personal?.querySelector("#emailFields");
  const celsContainer = container1Personal.querySelector("#celularFields");
  const celInput = container1Personal?.querySelector('input[type="tel"]');
  const btnAgregarCampoEmail = container1Personal?.querySelector(
    "#agregar-campo-email"
  );

  const btnAgregarCampoCelular = container1Personal.querySelector(
    "#agregar-campo-celular"
  );
  // PESTAÑA 2 (TRABAJO)
  // const btnPrev = document.getElementById("btn-prev"); //
  const btnCancelStep = document.getElementById("btn-cancel-step"); //
  const btnSave = document.getElementById("btn-submit"); //
  const spinner = btnSave.querySelector(".spinner");

  const inputRuc = document.getElementById("RUC");

  inputRuc.addEventListener("keypress", function (e) {
    if (/^[a-zA-Z]$/.test(e.key)) {
      e.preventDefault();
    }
  });
  // PESTAÑA 3 (HISTORIAL STATUS)
  const btnAgregarHStatus = document.getElementById("btnAgregarHistorial");
  const textAreaHStatus = container3Status?.querySelector("textarea");
  const listContainerHStatus = container3Status?.querySelector(
    "#lista-historial-status"
  );
  // PESTAÑA 4 (HISTORIAL ANTECEDENTES)
  const btnAgregarHAntecedentes = document.getElementById(
    "btnAgregarAntecedente"
  );
  const listContainerHAnte = container4Ante?.querySelector(
    "#lista-historial-antecedentes"
  );
  // PESTAÑA 5 (PERFIL)
  const camposMapping = {
    "TIPO DE CLIENTE": "tipo_cliente",
    "POLÍTICA DE PAGO": "politica_pago",
    "TRABAJA CON PROVEEDORES": "trabaja_proveedores",
    "PROCEDIM. ESPECIAL EN FACTURACIÓN Y DESPACHO": "procedimiento_facturacion",
    "FRECUENCIA DE COMPRA": "frecuencia_compra",
    ADICIONALES: "adicionales",
  };

  function cerrarModalCliente() {
    modal.classList.toggle("hidden", true);
    document.body.style.overflow = "";
  }
  //
  // INSTANCIAS
  //
  [btnCerrarModal, btnCancelStep].forEach((b) =>
    b?.addEventListener("click", ()=>{          
      if(multicreated){
        btnUpdate?.click();
        multicreated=false;
      }
      cerrarModalCliente();
    })
  );

  function enfocarPrimerCampoVacio(container, nombresCampos) {
    for (const nombre of nombresCampos) {
      const campo = container.querySelector(`[name="${nombre}"]`);
      if (campo && campo.offsetParent !== null) {
        // offsetParent !== null => visible
        const valor =
          campo.type === "checkbox" || campo.type === "radio"
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
    if (seccionAct.value === tabName) return; // Si ya está en la pestaña, no hacer nada
    seccionAct.value = tabName;
    tabBtns.forEach((btn) => {
      const active = btn.dataset.tab === tabName;
      btn.classList.toggle("font-semibold", active);
      btn.classList.toggle("border-l", active);
      btn.classList.toggle("border-t", active);
      btn.classList.toggle("border-r", active);
      btn.classList.toggle("rounded-t", active);
      btn.classList.toggle("text-gray-600", !active);
    });
    contentContainers.forEach((tc) => {
      if (tc.id !== "tab-perfil")
        tc.classList.toggle("hidden", tc.id !== `tab-${tabName}`);
    });
    // RECORRER LOS CAMPOS PARA ESTABLECER FOCUS CON LA FUNCION 'enfocarPrimerCampoVacio'
    const focusNames = [
      ["nombres", "apellidos", "email-1"],
      ["razon"],
      ["nuevo_status"],
      ["nuevo_antecedente"],
      Object.values(camposMapping),
    ];
    const index = tabBtnsData.indexOf(tabName);
    const container = contentContainers[index];
    const campos = focusNames[index];
    enfocarPrimerCampoVacio(container, campos);
  }
  tabBtns?.forEach((btn) => {
    btn.addEventListener("click", () => {
      const t = btn.dataset.tab;
      // if (form.dataset.mode !== "create") {
      //   form.dataset.mode = "edit";
      // }
      switchTo(t);
    });
  });
  // btnPrev.addEventListener("click", () => switchTo(tab1Personal)); // Paso ← Personal
  btnUpdate1.addEventListener("click", () => btnSave.click()); // Solo en EDITAR CLIENTE

  // (PERSONAL)
  function validarNumerosDuplicados() {
    const inputs = celsContainer.querySelectorAll('input[type="tel"]');
    const mensaje = container1Personal?.querySelector("#mensaje-cels");
    const valores = [];
    let duplicado = false;

    inputs.forEach((input) => {
      const valor = input.value.trim();
      input.classList.remove("border-red-500", "focus:ring-red-500");

      if (valor) {
        if (valores.includes(valor)) {
          input.classList.add("border-red-500", "focus:ring-red-500");
          duplicado = true;
        } else {
          valores.push(valor);
        }
      }
    });

    if (duplicado) {
      mensaje.textContent = "❌ Número duplicado. Ya ha sido ingresado.";
      mensaje.classList.remove("hidden");
      mensaje.classList.add("text-red-600");
    } else {
      mensaje.classList.add("hidden");
    }

    return !duplicado;
  }

  const regexTelefono = /^\+\d{6,15}$/;
  function validacionNumeros(event) {
    const input = event.target;
    const mensaje = container1Personal?.querySelector("#mensaje-cels");
    const valor = input.value.trim();

    if (!mensaje) return;

    // Limpiar clases previas
    input.classList.remove(
      "border-green-500",
      "border-red-500",
      "focus:ring-green-500",
      "focus:ring-primary",
      "focus:ring-red-500"
    );
    mensaje.classList.add("hidden"); // Ocultar siempre al inicio

    if (valor === "") {
      // No mostramos mensaje si está vacío
      input.classList.add("border-gray-300", "focus:ring-primary");
      return;
    }
    const esValido = regexTelefono.test(valor);
    const hayDuplicado = !validarNumerosDuplicados();

    if (regexTelefono.test(valor)) {
      // ✅ Número válido → sin mensaje
      input.classList.add("border-green-500", "focus:ring-green-500");
    } else {
      // ❌ Número inválido → mostrar mensaje rojo
      input.classList.add("border-red-500", "focus:ring-red-500");
      mensaje.textContent = "❌ Formato inválido. Ej: +51912345678";
      mensaje.classList.remove("hidden");
      mensaje.classList.add("text-red-600");
    }
  }
  function vaciarFormularioCliente() {
    cleanEmailsContainer();
    cleanCelularesContainer();
    Array.from(form.elements).forEach((el) => {
      if (
        ["INPUT", "TEXTAREA", "SELECT"].includes(el.tagName) &&
        !["button", "submit", "reset"].includes(el.type)
      ) {
        if (el.type === "checkbox") {
          el.checked = false;
        }
        if (el.name !== "fatencion" && el.name !== "actual") {
          if (el.name === "Cuenta[]") {
            el.parentElement.parentElement.parentElement.classList.toggle("hidden", false);
          } else {
            el.value = el.name === "idcliente" ? "AUTO" : "";
          }
        } else {
          const fechaHoy = new Date();
          const year = fechaHoy.getFullYear();
          const month = String(fechaHoy.getMonth() + 1).padStart(2, "0"); // Los meses van de 0 a 11
          const day = String(fechaHoy.getDate()).padStart(2, "0");
          const fechaFormateada = `${day}-${month}-${year}`;
          el.value = fechaFormateada;
        }
      }
    });
  }
  // Abrir modal para AGREGAR Cliente
  window.abrirModalAgregar = function (clienteData = null) {
    form.dataset.mode = "create";
    // tabC5.querySelectorAll('textarea[required]')?.forEach(i => i.removeAttribute('required'));
    vaciarFormularioCliente();
    btnUpdate1.classList.toggle("hidden", true);
    btnSave.textContent="Guardar Cliente";
    // CAMBIAR TITULO A AGREGAR CLIENTE
    tituloModal.innerHTML =
      '<i class="fas fa-user-plus text-xl mr-5"></i>Agregar Cliente';
    // MOSTRAMOS EL CONTENIDO VACIO EN Historial de Status y Antecedentes
    [container3Status, container4Ante].forEach((tab) => {
      const divs = tab.querySelectorAll("div");
      divs.forEach((div) => div.classList.toggle("hidden", true));
      const existingPlaceholder = tab.querySelector(".placeholder-create");
      // Si no existe aún, añadir placeholder
      if (!existingPlaceholder) {
        const nombreTab = tab.id.replace("tab-", "").replace("-", " de ");
        const ph = document.createElement("div");
        ph.className = "p-4 bg-gray-100 rounded text-center placeholder-create";
        ph.innerHTML = `<p class="text-gray-700">Contenido de ${nombreTab}</p>`;
        tab.appendChild(ph);
      } else {
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
  };
  btnModalAgregar?.addEventListener("click", () => window.abrirModalAgregar());
  // Funcionalidades para mas Email
  function cleanEmailsContainer() {
    const containers =
      emailsContainer?.querySelectorAll("div.flex.items-center.gap-2") || [];
    // Convertir a array y eliminar todos excepto el primero
    if (containers.length > 1) {
      containers.forEach((el, index) => {
        if (index > 0) el.remove();
      });
    }
  }

  function cleanCelularesContainer() {
    const containers =
      celsContainer?.querySelectorAll("div.flex.items-center.gap-2") || [];
    // Convertir a array y eliminar todos excepto el primero
    if (containers.length > 1) {
      containers.forEach((el, index) => {
        if (index > 0) el.remove();
      });
    }
  }
  let totalEmails = 1;
  let totalTelefonos = 1;
  function addEmailsContainers(values = [], clean = false) {
    if (!emailsContainer || !container1Personal) return;

    function orderEmailNames() {
      const emailsInput = emailsContainer.querySelectorAll("input[type=email]");
      emailsInput.forEach((input, index) => {
        input.name = `email-${index + 1}`;
      });
      totalEmails = emailsInput.length;
    }

    if (clean) cleanEmailsContainer();

    const original = container1Personal.querySelector("input[type=email]");
    if (!original) return;

    // Si hay al menos un valor no vacío
    if (values.length > 0) {
      // Establecer el primer valor en el input original
      original.value = values[0];

      // Agregar el resto
      values.slice(1).forEach((value) => {
        const clonInput = original.cloneNode(true);
        clonInput.value = value;

        const container = document.createElement("div");
        container.className = original.parentElement.className;

        const btnEliminar = document.createElement("button");
        btnEliminar.type = "button";
        btnEliminar.title = "Eliminar este campo";
        btnEliminar.className =
          "mr-1.5 p-1 bg-red-100 rounded-full text-red-600 hover:bg-red-200 transition-transform hover:scale-110";
        btnEliminar.onclick = () => container.remove() || orderEmailNames();

        const icon = document.createElementNS(
          "http://www.w3.org/2000/svg",
          "svg"
        );
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
      const currentEmails =
        emailsContainer.querySelectorAll("input[type=email]");
      if (currentEmails.length >= 5) {
        Swal.fire({
          icon: "warning",
          title: "Máximo alcanzado",
          text: "Solo puedes agregar hasta 5 correos electrónicos.",
          confirmButtonText: "Aceptar",
          confirmButtonColor: "#f44336",
        });
        return;
      } else {
        addEmailsContainers([original.value, ""]);
      }
    }
    orderEmailNames();
  }
  btnAgregarCampoEmail?.addEventListener("click", () => {
    addEmailsContainers();
  });

  // funcion agregar mas campos de telefono
  function addCelsContainers(values = [], clean = false) {
    if (!celsContainer || !container1Personal) return;

    function orderCelNames() {
      const celsInputs = celsContainer.querySelectorAll('input[type="tel"]');
      celsInputs.forEach((input, index) => {
        input.name = index === 0 ? "celular" : `celular${index + 1}`;
      });
      totalTelefonos = celsInputs.length;
    }

    if (clean) cleanCelularesContainer();

    const original = container1Personal.querySelector('input[type="tel"]');
    if (!original) return;

    const existingAlternativosLabel = celsContainer.querySelector(
      "#label-alternativos"
    );

    if (values.length > 0) {
      // Solo establece el primer valor en el campo original
      original.value = values[0];

      // Si no existe aún el label de alternativos y hay más de 1 valor
      if (!existingAlternativosLabel && values.length > 1) {
        const labelAlternativos = document.createElement("label");
        labelAlternativos.id = "label-alternativos";
        labelAlternativos.textContent = "Celular Alternativo";
        labelAlternativos.className =
          "block text-xs font-medium text-gray-700 mb-0.5";
        celsContainer.appendChild(labelAlternativos);
      }

      // El resto se clonan
      values.slice(1).forEach((value) => {
        const clonInput = original.cloneNode(true);
        clonInput.value = value;

        const container = document.createElement("div");
        container.className =
          "grupo-telefono " + original.parentElement.className;

        const btnEliminar = document.createElement("button");
        btnEliminar.type = "button";
        btnEliminar.title = "Eliminar este campo";
        btnEliminar.className =
          "mr-1.5 p-1 bg-red-100 rounded-full text-red-600 hover:bg-red-200 transition-transform hover:scale-110";
        btnEliminar.onclick = () => {
          container.remove();
          orderCelNames();

          // Si ya no hay más alternativos, eliminamos el label
          const restantes = celsContainer.querySelectorAll('input[type="tel"]');
          if (restantes.length <= 1) {
            const label = celsContainer.querySelector("#label-alternativos");
            if (label) label.remove();
          }
        };

        const icon = document.createElementNS(
          "http://www.w3.org/2000/svg",
          "svg"
        );
        icon.setAttribute("xmlns", "http://www.w3.org/2000/svg");
        icon.setAttribute("viewBox", "0 0 24 24");
        icon.setAttribute("fill", "none");
        icon.setAttribute("stroke", "currentColor");
        icon.setAttribute("class", "w-3.5 h-3.5 m-0");
        icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>`;

        btnEliminar.appendChild(icon);
        container.appendChild(clonInput);
        container.appendChild(btnEliminar);

        clonInput.addEventListener("input", validacionNumeros);

        celsContainer?.appendChild(container);
      });
    } else {
      const currentTelefonos =
        celsContainer.querySelectorAll('input[type="tel"]');
      if (currentTelefonos.length >= 4) {
        Swal.fire({
          icon: "warning",
          title: "Máximo alcanzado",
          text: "Solo puedes agregar hasta 4 números de celular.",
          confirmButtonText: "Aceptar",
          confirmButtonColor: "#f44336",
        });
        return;
      } else {
        addCelsContainers([original.value, ""]);
      }
    }

    orderCelNames();
  }
  function resetearCamposCelulares() {
    const alternativos = celsContainer.querySelectorAll(".grupo-telefono");
    alternativos.forEach((div) => div.remove());

    const inputPrincipal = container1Personal.querySelector(
      'input[name="celular"]'
    );
    if (inputPrincipal) inputPrincipal.value = "";

    const mensaje = document.getElementById("mensaje-cels");
    if (mensaje) mensaje.textContent = "";

    const label = celsContainer.querySelector("#label-alternativos");
    if (label) label.remove();
    if (inputPrincipal) {
      inputPrincipal.value = "";
      inputPrincipal.classList.remove("border-red-500", "border-green-500");
      inputPrincipal.setCustomValidity("");
      inputPrincipal.reportValidity();
    }

    totalTelefonos = 1;
  }
  document.getElementById("btnCerrarModal").addEventListener("click", () => {
    modal.classList.add("hidden");
    document.body.style.overflow = "";

    resetearCamposCelulares();

    const mensaje = document.getElementById("mensaje-cels");
    if (mensaje) mensaje.textContent = "";

    form.reset();

    tituloModal.innerHTML =
      '<i class="fas fa-user-plus text-xl mr-5"></i>Cliente';
  });

  celInput.addEventListener("input", validacionNumeros);
  btnAgregarCampoCelular?.addEventListener("click", () => addCelsContainers());

  function llenarFormulario(data) {
    data["Cuenta"] = "";
    listaCels = [""];
    Object.entries(data).forEach(([key, value]) => {
      // Excepción para el campo "email" si viene como JSON
      if (key === "email") {
        let lista = [""];
        if (value && value.trim() !== "") {
          try {
            lista = value.split(", ").map((email) => email.trim());
          } catch (e) {
            console.error("JSON de 'email' inválido:", e);
          }
        }
        addEmailsContainers(lista, true);
        return;
      } else if (key.includes("celular")) {
        if (value && value.trim() !== "") {
          if (key === "celular") {
            listaCels[0] = value;
          } else {
            listaCels.push(value);
          }
        }
        if (key === "celular4") {
          addCelsContainers(listaCels, true);
        }
      } else if(key === "Cuenta"){
        const campo = form.querySelector(`[name="Cuenta[]"]`);
        campo.parentElement.parentElement.parentElement.classList.toggle("hidden", true);
      }

      const campos = form.querySelectorAll(`[name="${key}"]`);
      if (!campos.length) return;

      campos.forEach((campo) => {
        const tag = campo.tagName;
        const type = campo.type;

        if (type === "checkbox") {
          campo.checked = value === "1" || value === 1 || value === true;
        } else if (type === "radio") {
          campo.checked = campo.value == value;
        } else if (tag === "SELECT") {
          campo.value = value;
        } else if (campo.classList.contains("datepicker") && campo._flatpickr) {
          campo._flatpickr.setDate(value, true);
        } else {
          campo.value = value;
        }
      });
      const dateActual = form.querySelector('[name="actual"]');
      if (dateActual) {
        const fechaHoy = new Date();
        const year = fechaHoy.getFullYear();
        const month = String(fechaHoy.getMonth() + 1).padStart(2, "0"); // Los meses van de 0 a 11
        const day = String(fechaHoy.getDate()).padStart(2, "0");
        const fechaFormateada = `${day}-${month}-${year}`;
        dateActual.value = fechaFormateada;
      }
    });
  }

  function limpiarCamposPerfil() {
    //LIMPIAR TODOS LOS CAMPOS TEXTAREA EN LA PESTAÑA DE PERFIL
    const camposPerfil = container5Perfil.querySelectorAll("textarea");
    camposPerfil.forEach((campo) => (campo.value = ""));
  }
  function llenarHistorial(data, tab) {
    const indexContainer = tabBtnsData.indexOf(tab);
    const container = contentContainers[indexContainer];
    const divs = container.querySelectorAll("div") || [];
    divs.forEach((div) => div.classList.toggle("hidden", false));
    const listContainer = container.querySelector(`#lista-${tab}`);
    listContainer.innerHTML = "";
    const existingPlaceholder = container.querySelector(".placeholder-create");
    existingPlaceholder?.classList.toggle("hidden", true);
    if (Array.isArray(data) && data.length > 0) {
      data.forEach((item) => {
        const block = document.createElement("div");
        block.className =
          "bg-white p-4 border-l-4 border-orange-500 rounded-md shadow-sm";
        block.innerHTML = `
                    <div class="flex justify-between items-center mb-1">
                    <p class="text-sm font-semibold text-gray-800">${item.usuario}</p>
                    <span class="text-xs text-gray-500">${item.fecha}</span>
                    </div>
                    <p class="text-sm text-gray-600 whitespace-pre-line">${item.descripcion}</p>
                `;
        listContainer.appendChild(block);
      });
    } else {
      listContainer.innerHTML = `<p class="text-sm text-gray-500 italic">No hay historial registrado.</p>`;
    }
  }
  function llenarPerfil(data) {
    let perfilData = {};
    if (data && data.trim() !== "") {
      try {
        perfilData = JSON.parse(data);
      } catch {
        perfilData = {};
      }
    }
    for (const [titulo, name] of Object.entries(camposMapping)) {
      const valor = perfilData[titulo] ?? "";
      const textarea = container5Perfil?.querySelector(`[name="${name}"]`);
      if (textarea) {
        textarea.value = valor;
      }
    }
  }
  // Abrir modal para EDITAR Cliente
  window.abrirModalEditar = function (clienteId, tabContent = 1) {
    form.dataset.mode = "edit";
    btnUpdate1.classList.toggle("hidden", false);
    btnSave.textContent="Actualizar Cliente";
    // CAMBIAR A ACCION EDITAR AL FORM
    fetch(base + "controller/cliente/obtener.php?id=" + clienteId)
      .then((res) => res.json())
      .then((data) => {
        const cliente = data.cliente;
        if (!data.success || !cliente) {
          throw new Error("Cliente no encontrado");
        }
        // Mostrar TODAS las pestañas, incluye botón Perfil
        tabBtns.forEach((btn) =>
          btn.parentElement.classList.toggle("hidden", false)
        );
        // Ir a pestaña indicada por tabContent
        const idx = Math.max(0, tabContent - 1);
        if (tabBtns[idx]) tabBtns[idx].click();

        tituloModal.innerHTML =
          '<i class="fas fa-user-edit text-xl mr-5"></i>Editar Cliente';
        // LLENAR PERSONAL y TRABAJO
        llenarFormulario(cliente);
        // LLENAR HISTORIAL DE STATUS
        llenarHistorial(cliente.historial, tab3Status);
        // LLENAR HISTORIAL DE ANTECEDENTES
        llenarHistorial(cliente.antecedentes, tab4Ante);
        // LLENAR PERFIL
        llenarPerfil(cliente.perfil); // cliente.perfil
        modal.classList.remove("hidden");
        document.body.style.overflow = "hidden";
      });
  };
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
    container2Trabajo
      ?.querySelectorAll('input[type="checkbox"]')
      .forEach((checkbox) => {        
        if(checkbox.name !== "Cuenta[]"){
          if (!checkbox.checked) {
            // Si no está marcado, agregamos un input hidden con valor 0
            const hidden = document.createElement("input");
            hidden.type = "hidden";
            hidden.name = checkbox.name;
            hidden.value = "0";
            form.appendChild(hidden);
          } else {
            // Si está marcado, aseguramos que tenga value="1"
            checkbox.value = "1";
          }
        }
      });
  }
  //
  // (HISTORIAL STATUS)
  btnAgregarHStatus?.addEventListener("click", async () => {
    const idcliente = document.querySelector('input[name="idcliente"]').value;
    const texto = document
      .querySelector("#tab-historial-status textarea")
      .value.trim();
    if (!texto) {
      Swal.fire({
        icon: "warning",
        title: "Campo vacío",
        text: "Por favor, escribe un status.",
        confirmButtonText: "Aceptar",
        confirmButtonColor: "#f44336",
      });
      return;
    }

    if (!idcliente || idcliente === "AUTO") {
      Swal.fire({
        icon: "warning",
        title: "Error",
        text: "ID de cliente inválido.",
        confirmButtonText: "Aceptar",
        confirmButtonColor: "#f44336",
      });
      return;
    }
    fetch(base + "controller/historial/crear.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        tabla: "clientes",
        idtabla: Number(idcliente),
        status: texto,
      }),
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          // Eliminar Contenedor con texto 'No hay historial registrado'
          listContainerHStatus
            ?.querySelector("p.text-sm.text-gray-500.italic")
            ?.remove();

          textAreaHStatus.value = "";
          const fecha = data.data?.fecha || new Date().toLocaleString();
          const usuario = "Tú";
          const descripcion = data.data?.descripcion || texto;
          const nuevo = document.createElement("div");
          nuevo.className =
            "bg-white p-4 border-l-4 border-orange-500 rounded-md shadow-sm";
          nuevo.innerHTML = ` <div class="flex justify-between items-center mb-1">
                                        <p class="text-sm font-semibold text-gray-800">${usuario}</p>
                                        <span class="text-xs text-gray-500">${fecha}</span>
                                    </div>
                                    <p class="text-sm text-gray-600 whitespace-pre-line">${descripcion}</p>`;
          Swal.fire({
            icon: "success",
            title: "¡Guardado!",
            text: data.message,
            timer: 2000,
            showConfirmButton: false,
          });
          listContainerHStatus?.prepend(nuevo);
          // ACTUALIZAR TABLA y CERRAR MODAL
          btnUpdate?.click();
          btnCerrarModal?.click();
        } else {
          Swal.fire({
            icon: "error",
            title: "Error al guardar",
            text: data.message,
          });
        }
      });
  });

  // Función para cargar los antecedentes
  function cargarAntecedentes(idcliente) {
    const listaAntecedentes = document.getElementById(
      "lista-historial-antecedentes"
    );
    listaAntecedentes.innerHTML =
      '<p class="text-sm text-gray-500">Cargando...</p>';

    fetch(
      base +
        `controller/antecedentes/listar_antecedentes.php?idcliente=${idcliente}`
    )
      .then((res) => res.json())
      .then((data) => {
        listaAntecedentes.innerHTML = "";
        if (data.success && data.data.length > 0) {
          data.data.sort((a, b) => new Date(b.fecha) - new Date(a.fecha));
          data.data.forEach((antecedente) => {
            const nuevo = document.createElement("div");
            nuevo.className =
              "bg-white p-4 border-l-4 border-orange-500 rounded-md shadow-sm";
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
          listaAntecedentes.innerHTML =
            '<p class="text-sm text-gray-500">No hay antecedentes registrados.</p>';
        }
      })
      .catch((error) => {
        console.error("Error al cargar antecedentes:", error);
        listaAntecedentes.innerHTML =
          '<p class="text-sm text-red-500">Error al cargar los antecedentes.</p>';
        Swal.fire({
          icon: "error",
          title: "Error",
          text: "No se pudo cargar los antecedentes.",
          confirmButtonText: "Aceptar",
          confirmButtonColor: "#f44336",
        });
      });
  }

  // Agregar antecedente
  btnAgregarHAntecedentes?.addEventListener("click", async () => {
    const idcliente = document.querySelector(
      '#tab-historial-antecedentes input[name="idcliente"]'
    ).value;
    const texto = document
      .querySelector("#tab-historial-antecedentes textarea")
      .value.trim();

    if (!texto) {
      Swal.fire({
        icon: "warning",
        title: "Campo vacío",
        text: "Por favor, escribe un antecedente.",
        confirmButtonText: "Aceptar",
        confirmButtonColor: "#f44336",
      });
      return;
    }

    if (!idcliente || idcliente === "AUTO") {
      Swal.fire({
        icon: "warning",
        title: "Error",
        text: "ID de cliente inválido.",
        confirmButtonText: "Aceptar",
        confirmButtonColor: "#f44336",
      });
      return;
    }
    fetch(base + "controller/antecedentes/crear_antecedente.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ status: texto, idcliente }),
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          // Eliminar Contenedor con texto 'No hay historial registrado'
          listContainerHAnte
            ?.querySelector("p.text-sm.text-gray-500.italic")
            ?.remove();

          document.querySelector("#tab-historial-antecedentes textarea").value =
            "";
          const fecha = data.data?.fecha || new Date().toLocaleString();
          const usuario = "Tú";
          const descripcion = data.data?.descripcion || texto;
          const nuevo = document.createElement("div");
          nuevo.className =
            "bg-white p-4 border-l-4 border-orange-500 rounded-md shadow-sm";
          nuevo.innerHTML = `
                        <div class="flex justify-between items-center mb-1">
                        <p class="text-sm font-semibold text-gray-800">${usuario}</p>
                        <span class="text-xs text-gray-500">${fecha}</span>
                        </div>
                        <p class="text-sm text-gray-600 whitespace-pre-line">${descripcion}</p>
                    `;
          Swal.fire({
            icon: "success",
            title: "¡Guardado!",
            text: data.message,
            timer: 2000,
            showConfirmButton: false,
          });
          listContainerHAnte?.prepend(nuevo);
          // ACTUALIZAR TABLA y CERRAR MODAL
          btnUpdate?.click();
          btnCerrarModal?.click();
        } else {
          Swal.fire({
            icon: "error",
            title: "Error al guardar",
            text: data.message,
          });
        }
      })
      .catch((error) => {
        console.error("Error al agregar antecedente:", error);
        Swal.fire({
          icon: "error",
          title: "Error",
          text: "No se pudo agregar el antecedente.",
          confirmButtonText: "Aceptar",
          confirmButtonColor: "#f44336",
        });
      });
  });
  // Función para validar correos duplicados
  function validarEmailsDuplicados() {
    const emailsInputs = emailsContainer.querySelectorAll(
      'input[type="email"]'
    );
    const valores = [];
    let duplicado = false;

    emailsInputs.forEach((input) => {
      const valor = input.value.trim().toLowerCase(); // comparación insensible a mayúsculas
      input.classList.remove("border-red-500", "focus:ring-red-500");

      if (valor) {
        if (valores.includes(valor)) {
          input.classList.add("border-red-500", "focus:ring-red-500");
          duplicado = true;
        } else {
          valores.push(valor);
        }
      }
    });

    return !duplicado; // Retorna true si no hay duplicados
  }
  let multicreated = false;
  // Guardar información de Cliente (Personal y Trabajo)
  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    if (!validarNumerosDuplicados()) {
      Swal.fire({
        icon: "warning",
        title: "Número duplicado",
        text: "Uno o más números de celular ya están repetidos. Por favor corrige antes de continuar.",
        confirmButtonText: "Aceptar",
        confirmButtonColor: "#f44336",
      });
      return; // Detener envío del formulario
    }
    if (!validarEmailsDuplicados()) {
      Swal.fire({
        icon: "warning",
        title: "Email duplicado",
        text: "Uno o más correos electrónicos ya están repetidos. Por favor corrige antes de continuar.",
        confirmButtonText: "Aceptar",
        confirmButtonColor: "#f44336",
      }).then(() => {
        switchTo(tab1Personal); // Opcional: te lleva a la pestaña Personal para corregirlo fácilmente
      });
      return;
    }

    const dataset = form.dataset.mode;
    // Al hacer clic en el botón Guardar Cliente y Guardar Perfil
    if (["edit", "create"].includes(dataset) && !form.checkValidity()) {
      const labelsEmails = Array.from(
        { length: totalEmails },
        (_, i) => `email-${i + 1}`
      );
      const camposPersonales = [...labelsEmails, "nombres", "apellidos"];
      const invalidElement = form.querySelector(":invalid");

      if (invalidElement) {
        // Buscar la pestaña contenedora (ajustar al selector real de tus pestañas)
        const tabContent = invalidElement.closest(".tab-content");
        const index = tabBtnsData.indexOf(tabContent.id.replace("tab-", ""));
        switchTo(tabBtnsData[index]);
        // Enfocar el elemento inválido para mostrar el mensaje
        invalidElement.focus();
        const campo = camposPersonales.includes(invalidElement.name)
          ? "que tenga *"
          : `"${invalidElement.name}"`;
        const text = `Por favor, completa correctamente el campo ${campo}`;
        Swal.fire({
          icon: "warning",
          title: "Rellena los campos obligatorios",
          html: `<p> ${text}</p>`,
          confirmButtonText: "Aceptar",
          confirmButtonColor: "#f44336",
          width: "23rem", // Ancho actual
          padding: "0.3rem",
          customClass: {
            icon: "swal-icon-small",
            title: "swal-title-small",
          },
        });
        // Aplicar estilos directamente
        document.querySelector(".swal-icon-small").style.fontSize = "1.2rem"; // Icono más pequeño
        document.querySelector(".swal-title-small").style.fontSize = "1.1rem";
        return;
      }
    }
    // Obtener todos los correos del formulario
    const inputsEmails = emailsContainer.querySelectorAll(
      'input[type="email"]'
    );
    const correos = Array.from(inputsEmails)
      .map((input) => input.value.trim().toLowerCase())
      .filter((email) => email !== "");

    // Validación por backend
    try {
      const data = new URLSearchParams();
      data.append("correos", correos.join(",")); // Enviar como cadena separada por comas
      if (form.dataset.mode === "edit" || form.dataset.mode === "edit-perfil") {
        data.append(
          "idcliente",
          form.querySelector('[name="idcliente"]').value
        );
      }

      const resp = await fetch(
        `${base}controller/cliente.php?action=validar_multiples_emails`,
        {
          method: "POST",
          body: data,
        }
      );

      const resultado = await resp.json();
      if (resultado.duplicados && resultado.duplicados.length > 0) {
        const lista = resultado.duplicados
          .map(
            (d) =>
              `<li><strong>${d.email}</strong> ya está en uso por ${d.nombres} ${d.apellidos} (ID ${d.idcliente})</li>`
          )
          .join("");
        Swal.fire({
          icon: "warning",
          title: "Correos duplicados",
          html: `<ul class="text-left text-sm">${lista}</ul>`,
          confirmButtonText: "Aceptar",
          confirmButtonColor: "#f44336",
        });
        return; // ❌ Cancela el guardado
      }
    } catch (err) {
      console.error("Error al validar correos:", err);
      Swal.fire({
        icon: "error",
        title: "Error de red",
        text: "No se pudo validar los correos. Intenta nuevamente.",
        confirmButtonText: "Aceptar",
        confirmButtonColor: "#f44336",
      });
      return;
    }
    let inputEmailsFinal = form.querySelector('input[name="email"]');
    if (!inputEmailsFinal) {
      inputEmailsFinal = document.createElement("input");
      inputEmailsFinal.type = "hidden";
      inputEmailsFinal.name = "email";
      form.appendChild(inputEmailsFinal);
    }
    inputEmailsFinal.value = correos.join(",");

    try {
      prepararCheckboxes();
      const formData = new FormData(form);

      // Obtener los checkboxes seleccionados de Cuenta[]
      const seleccionados = formData.getAll('Cuenta[]'); // ← Aquí sí tienes TODOS los seleccionados

      // Definir cuentas disponibles (puedes hacerlo dinámico si gustas)
      const cuentasDisponibles = ['compina', 'compipro'];

      // Crear estructura de cuentas
      const cuentas = {};
      cuentasDisponibles.forEach(cuenta => {
        cuentas[cuenta] = seleccionados.includes(cuenta) ? 1 : 0;
      });
      // Limpiar el campo redundante
      delete formData['Cuenta[]'];

      formData.append("cuentas", JSON.stringify(cuentas));

      // for (const [key, value] of formData.entries()) {
      //     console.log(`${key}: ${value}`);
      // }

      const actionType = form.dataset.mode;
      const action = base + "controller/cliente.php?action=" + actionType;
      // const action = `${base}controller/cliente/notion/sync_to_notion.php?action=${actionType}`;

      if (btnSave) btnSave.disabled = true;
      if (btnCancelStep) btnCancelStep.disabled = true;
      spinner.classList.toggle("hidden", false);
      const resp = await fetch(action, { method: "POST", body: formData });
      const text = await resp.text();
      // Verificar si la respuesta parece ser HTML
      if (
        text.trim().startsWith("<!DOCTYPE") ||
        text.includes("<html") ||
        text.includes("<body")
      ) {
        // Parsear el HTML para extraer el mensaje de error
        const parser = new DOMParser();
        const doc = parser.parseFromString(text, "text/html");
        // Buscar un elemento que contenga el mensaje de error (ajusta el selector según tu HTML)
        const errorMessage =
          doc.querySelector("p")?.innerHTML.trim() ||
          "Error desconocido en la respuesta del servidor";

        Swal.fire({
          icon: "error",
          title: "Error en el servidor",
          html: `<style>code.url { background-color: #eeeeee; font-family: monospace; padding: 0 2px; }</style>${errorMessage}`,
        });
        return; // Salir para no intentar parsear HTML como JSON
      }

      const json = JSON.parse(text);
      multicreated = false;
      if (json.success) {
        Swal.fire({
          icon: "success",
          title: "¡Guardado!",
          text: json.message,
        }).then(() => {
          if (typeof renderTable === "function"){
            cerrarModalCliente();
            renderTable();
          }else{
            let idcliente = "AUTO";
            if(form.dataset.mode === "edit"){
              cerrarModalCliente();
              btnUpdate?.click();
            } else if(json.id){
              idcliente= json.id;
              // Establecer ID
              form.dataset.mode = "edit-perfil";
            }else if(form.dataset.mode === "edit-perfil"){
              form.dataset.mode = "create";
              vaciarFormularioCliente();
              switchTo(tab1Personal);
            }
            multicreated = form.dataset.mode !== "edit";
            const inputs = document.getElementsByName("idcliente");
            inputs.forEach(input => input.value=idcliente);
          }
        });
        multicreated = multicreated && json.success;
      } else {
        const message =
          json.details?.error ?? json.message ?? "Error desconocido";
        Swal.fire({
          icon: "error",
          title: "Error al guardar",
          text: message,
        });
      }
    } catch (err) {
      Swal.fire({ icon: "error", title: "Error de red", text: err.message });
    } finally {
      if (btnSave) btnSave.disabled = false;
      if (btnCancelStep) btnCancelStep.disabled = false;
      spinner.classList.toggle("hidden", true);
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
        shorthand: ["Do", "Lu", "Ma", "Mi", "Ju", "Vi", "Sa"],
        longhand: [
          "Domingo",
          "Lunes",
          "Martes",
          "Miércoles",
          "Jueves",
          "Viernes",
          "Sábado",
        ],
      },
      months: {
        shorthand: [
          "Ene",
          "Feb",
          "Mar",
          "Abr",
          "May",
          "Jun",
          "Jul",
          "Ago",
          "Sep",
          "Oct",
          "Nov",
          "Dic",
        ],
        longhand: [
          "Enero",
          "Febrero",
          "Marzo",
          "Abril",
          "Mayo",
          "Junio",
          "Julio",
          "Agosto",
          "Septiembre",
          "Octubre",
          "Noviembre",
          "Diciembre",
        ],
      },
    },
  });
});
