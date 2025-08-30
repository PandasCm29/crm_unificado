import { paginacion, updateRowsPerPage } from "./paginacion.js";
// ======= 1) Referencias y variables globales =======
const modalEditar = document.getElementById("modalEditarRequerimiento");
const btnHist = modalEditar.querySelector("#btnAgregarHistorial");
const textAreaHist = modalEditar.querySelector("#textAreaHist");
const listHist = modalEditar.querySelector("#lista-historial-status");

function createTruncatedText(text, maxLength = 50, tipo = 'general') {
  if (!text || text.length <= maxLength) {
    return `<div class="max-w-[200px] text-[10px] leading-tight">${
      text || ""
    }</div>`;
  }
  const truncated = text.substring(0, maxLength);
  const uniqueId = "text_" + Math.random().toString(36).slice(2, 11);
  return `
    <div class="max-w-[200px] text-[10px] leading-tight">
      <span id="${uniqueId}_content">
        <span id="${uniqueId}_short" class="inline break-words">${truncated}...</span>
      </span>
      <br>
      <button type="button" 
              class="inline text-blue-600 hover:text-blue-800 hover:bg-blue-50 text-[9px] font-medium px-1 py-0.5 rounded transition-all duration-200 border-none bg-transparent cursor-pointer focus:outline-none hover:underline" 
              onclick="window.abrirModalTexto('${uniqueId}', '${tipo}', \`${text
    .replace(/`/g, "\\`")
    .replace(/\\/g, "\\\\")}\`)">
        Ver más
      </button>
    </div>
  `;
}
//Funcion para abrir modal con texto completo
window.abrirModalTexto = function (id, tipo, textoCompleto) {
  //Crear modal si no existe
  let modal = document.getElementById("modalTextoCompleto");
  if (!modal) {
    modal = document.createElement("div");
    modal.id = "modalTextoCompleto";
    modal.className =
      "fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50";
    modal.innerHTML = `
      <div class="bg-white rounded-lg shadow-2xl w-full max-w-2xl relative max-h-[90vh] overflow-y-auto">
        <!-- CABECERA -->
        <div class="bg-gray-500 text-white rounded-t-lg px-6 py-4 flex items-center justify-between">
          <h1 id="tituloModal" class="text-lg font-semibold">Contenido Completo</h1>
          <button id="cerrarModalTexto" class="text-white hover:text-gray-200">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
        
        <!-- CONTENIDO -->
        <div class="px-6 py-4">
          <div id="contenidoModalTexto" class="text-sm text-gray-700 whitespace-pre-wrap leading-relaxed">
          </div>
        </div>
        
        <!-- PIE -->
        <div class="px-6 py-4 bg-gray-50 rounded-b-lg flex justify-end">
          <button id="cerrarModalTexto2" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition">
            Cerrar
          </button>
        </div>
      </div>
    `;
    document.body.appendChild(modal);
    //Event listeners para cerrar
    modal.querySelector("#cerrarModalTexto").addEventListener("click", () => {
      modal.classList.add("hidden");
    });
    modal.querySelector("#cerrarModalTexto2").addEventListener("click", () => {
      modal.classList.add("hidden");
    });
    modal.addEventListener("click", (e) => {
      if (e.target === modal) modal.classList.add("hidden");
    });
  }
  const titulo = modal.querySelector("#tituloModal");
  const contenido = modal.querySelector("#contenidoModalTexto");
  switch (tipo) {
    case "asunto":
      titulo.textContent = "Requerimiento Completo";
      break;
    case "historial":
      titulo.textContent = "Historial de Status";
      break;
    default:
      titulo.textContent = "Contenido Completo";
  }
  contenido.textContent = textoCompleto;
  modal.classList.remove("hidden");
};
window.toggleText = function (id, verCompleto) {
  const shortElement = document.getElementById(id + "_short");
  const fullElement = document.getElementById(id + "_full");
  const btnMore = document.getElementById(id + "_btn_more");
  const btnLess = document.getElementById(id + "_btn_less");
  if (verCompleto) {
    shortElement.style.display = "none";
    fullElement.style.display = "inline";
    btnMore.style.display = "none";
    btnLess.style.display = "inline";
  } else {
    shortElement.style.display = "inline";
    fullElement.style.display = "none";
    btnMore.style.display = "inline";
    btnLess.style.display = "none";
  }
};
// ======= 2) Renderizar la tabla completa =======
window.renderTable = async function () {
  const tbody = document.getElementById("tableBody");
  tbody.innerHTML = "";

  try {
    const { page, limit } = paginacion.getQueryParams();
    const response = await fetch(
      `${base}controller/requerimientos/obtener.php?page=${page}&limit=${limit}`
    );
    const result = await response.json();
    if (!result.success)
      throw new Error(result.error || "Error al cargar datos");

    const consultas = result.data;
    const totalRecords = result.total || 0;
    paginacion.updatePaginationInfo(totalRecords);
   for (let i = 0; i < consultas.length; i++) {
  const row = consultas[i];
  const tr = document.createElement("tr");
  tr.innerHTML = `
    <td class="action-buttons align-top">
          <div class="flex flex-col gap-1 w-fit mb-1 px-0.5">
            <button class="client-button bg-orange-400 hover:bg-orange-500 text-white font-bold py-1 px-2 rounded text-xs uppercase leading-tight text-center transition-colors duration-200 whitespace-nowrap">
              Validando...
            </button>
            <button class="btn-editar-req bg-green-400 hover:bg-green-500 text-white font-bold py-1 px-2 rounded text-xs uppercase flex flex-col items-center justify-center leading-tight text-center transition-colors duration-200 whitespace-nowrap">
              Editar<br>Requerimiento
            </button>
            <div class="relative inline-block w-fit">
              <select class="status-select bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-2 rounded text-xs uppercase cursor-pointer transition-colors duration-200 border-none outline-none appearance-none text-left pr-6 whitespace-nowrap">

                <option value="1" class="bg-white text-black normal-case">Pendiente</option>
                <option value="2" class="bg-white text-black normal-case">Atendido</option>
                <option value="3" class="bg-white text-black normal-case">Se deja de atender</option>
                <option value="eliminar" class="bg-white text-black normal-case">Eliminar</option>
              </select>
              <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
              </div>
            </div>
          </div>
        </td>

<td class="text-[10px]">${row.idconsulta}</td>
    <td class="text-[10px]">${row.usuario || ""}</td>
    <td class="text-[10px]">${row.fecha_consulta ? fechaLarga(row.fecha_consulta) : ""}</td>
    <td class="text-[10px]">${row.empresa || ""}</td>
    <td>${createTruncatedText(row.asunto, 50, "asunto")}</td>
    <td class="text-[10px]">${row.derivado || ""}</td>   
    <td class="text-[10px]">
      ${createTruncatedText(row.historial_status, 60, "historial")}<br>
      <a href="#" class="text-blue-600 hover:underline ver-status-link text-[10px]" data-id="${row.idconsulta}">Ver status</a>
    </td>
    <td class="text-[10px]">${row.nombres || ""}</td>
    <td class="text-[10px]">${row.apellidos || ""}</td>
    <td class="text-[10px]">${row.fechaaviso ? fechaLarga(row.fechaaviso) : ""}</td>
    <td class="text-[10px]">${row.fatencion ? fechaLarga(row.fatencion) : ""}</td>
    <td class="text-[10px]">${row.email || ""}</td>
    <td class="text-[10px]">${row.direccion || ""}</td>
    <td class="text-[10px]">${row.telefono || ""}</td>
    <td class="text-[10px]">${row.celular || ""}</td>
    <td class="text-[10px]">
      ${row.archivo
        ? `<a href="${base}controller/requerimientos/${row.archivo}" download target="_blank" class="text-blue-600 underline">Descargar archivo</a>`
        : "Sin archivo"}
    </td> 
    <td class="text-[10px]">${row.archivo}</td>
  `;

  // Agregar bordes redondeados solo a la última fila
  if (i === consultas.length - 1) {
    const tds = tr.querySelectorAll("td");
    if (tds.length > 0) {
      tds[0].classList.add("rounded-bl-2xl");
      tds[tds.length - 1].classList.add("rounded-br-lg");
    }
  }

  tbody.appendChild(tr);
      // Actualizar botón cliente
      const clientButton = tr.querySelector(".client-button");
      actualizarBotonCliente(
        [row.email, row.telefono, row.celular],
        clientButton
      ).then((idcliente) => {
        clientButton.dataset.clientId = idcliente;
      });
      const editButton = tr.querySelector(".btn-editar-req");
      editButton.dataset.reqId = row.idconsulta;

      // Ajustar select al status actual
      const select = tr.querySelector(".status-select");
      select.value = row.status || "3";
    }
    paginacion.renderPaginationControls();

    setupEventListeners();
    setupStatusSelectListeners();
  } catch (error) {
    console.error("Error al cargar datos:", error);
    tbody.innerHTML =
      '<tr><td colspan="18" class="text-center text-red-500">Error al cargar los datos</td></tr>';
  }
};

paginacion.renderFunction = renderTable;
// ======= 4) Status select listeners =======
function setupStatusSelectListeners() {
  document.querySelectorAll(".status-select").forEach((select) => {
    const tr = select.closest("tr");

    function actualizarColorFila() {
      tr.classList.remove(
        "bg-gray-100",
        "bg-green-100",
        "bg-red-100",
        "bg-white"
      );
      switch (select.value) {
        case "eliminar":
          tr.classList.add("bg-gray-100");
          break;
        case "2":
          tr.classList.add("bg-green-100");
          break;
        case "1":
          tr.classList.add("bg-red-100");
          break;
        case "3":
        default:
          tr.classList.add("bg-white");
          break;
      }
    }

    actualizarColorFila();
    select.addEventListener("change", async () => {
      actualizarColorFila();
      const idConsulta = tr.querySelector("td:nth-child(2)").textContent.trim();
      let statusValue = select.value === "eliminar" ? null : select.value;

      if (select.value === "eliminar") {
        const confirmacion = await Swal.fire({
          title: "¿Seguro que deseas eliminar este requerimiento?",
          icon: "warning",
          showCancelButton: true,
          confirmButtonText: "Sí, eliminar",
          cancelButtonText: "Cancelar",
          confirmButtonColor: "#d33",
          cancelButtonColor: "#3085d6",
        });

        if (!confirmacion.isConfirmed) {
          select.value = select.dataset.currentStatus || "";
          actualizarColorFila();
          return;
        }

        await eliminarRequerimiento(idConsulta);
      } else if (statusValue !== select.dataset.currentStatus) {
        await actualizarStatus(idConsulta, statusValue);
        select.dataset.currentStatus = statusValue;
      }
    });
  });
}

// ======= 5) Event listeners generales =======
function setupEventListeners() {
  document.querySelectorAll(".btn-editar-req").forEach((btn) => {
    btn.addEventListener("click", handleEditarRequerimiento);
  });
  document.querySelectorAll(".client-button").forEach((btn) => {
    btn.addEventListener("click", handleClienteAction);
  });
  // Agrega este nuevo listener
  document.querySelectorAll(".ver-status-link").forEach((link) => {
    link.addEventListener("click", handleVerStatus);
  });
}

// ======= 6) Helpers: status text =======
function getStatusText(value) {
  switch (String(value)) {
    case "1":
      return "Pendiente";
    case "2":
      return "Atendido";
    case "3":
      return "Se deja de atender";
    default:
      return "Sin status";
  }
}

// ======= 7) Cliente action =======
function handleClienteAction(event) {
  const btn = event.target.closest(".client-button");
  if (!btn) return;
  const id = btn.dataset.clientId;
  // Lógica de redirección (editar o crear cliente)
  const isEditar = btn.textContent.includes("Editar");
  isEditar
    ? window.abrirModalEditar(id)
    : window.abrirModalAgregar(obtenerDatosFilaDesdeBoton(btn));
}

// ======= 8) Validación de cliente =======
async function validarClienteExiste([email, telefono, celular]) {
  try {
    const resp = await fetch(base + "controller/cliente/verificacion.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ email, telefono, celular }),
    });
    if (!resp.ok) return false;
    const data = await resp.json();
    return data;
  } catch {
    return false;
  }
}
async function actualizarBotonCliente(info, btn) {
  const data = await validarClienteExiste(info);
  const existe = (data && data.existe) || false;
  const color = existe ? "blue" : "green";
  const text = existe ? "Editar Cliente" : "Guardar Cliente";
  btn.textContent = text;
  btn.className = `client-button bg-${color}-400 hover:bg-${color}-500 text-white font-bold py-2 px-4 rounded text-xs uppercase`;
  return data.id;
}
function obtenerDatosFilaDesdeBoton(boton) {
  const fila = boton.closest("tr"); // Obtener la fila (tr) más cercana
  if (!fila) return null;

  const celdas = fila.querySelectorAll("td");

  return {
    nombres: celdas[8]?.textContent.trim(),
    apellidos: celdas[9]?.textContent.trim(),
    "email-1": celdas[12]?.textContent.trim(),
    telefono: celdas[14]?.textContent.trim(),
    celular: celdas[15]?.textContent.trim(),
  };
}

// ======= 9) Actualizar status via API =======
async function actualizarStatus(id, status) {
  try {
    const resp = await fetch(base+"controller/requerimientos/actualizar.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ action: "update_status", id, status }),
    });
    const json = await resp.json();
    if (!json.success) throw new Error(json.message);
    Swal.fire({
      icon: "success",
      title: "Status actualizado",
      timer: 2000,
      showConfirmButton: false,
    });
  } catch (e) {
    Swal.fire({ icon: "error", title: "Error", text: e.message });
  }
}

// ======= 10) Eliminar requerimiento =======
async function eliminarRequerimiento(id) {
  try {
    const resp = await fetch(base+"controller/requerimientos/actualizar.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ action: "delete", id }),
    });
    const json = await resp.json();
    if (!json.success) throw new Error(json.message);
    Swal.fire({
      icon: "success",
      title: "Eliminado",
      timer: 2000,
      showConfirmButton: false,
    });
    renderTable();
  } catch (e) {
    Swal.fire({ icon: "error", title: "Error", text: e.message });
  }
}

// ======= 11) Handle abrir modal editar + render historial =======
async function handleEditarRequerimiento(event) {
  const btn = event.target;
  if (!btn) return;
  const id = btn.dataset.reqId;

  // Poblar datos y historial
  try {
    const resp = await fetch(
      `${base}controller/requerimientos/obtener.php?id=${id}`
    );
    const json = await resp.json();
    if (!json.success) throw new Error(json.message);
    const data = json.data;

    // 1) Form
    Object.entries(data).forEach(([k, v]) => {
      const fld = modalEditar.querySelector(`[name="${k}"]`);
      if (fld) fld.value = v ?? "";
    });
    modalEditar.querySelector('[name="idconsulta"]').value = id;

    // 2) Historial
    renderHistorial(data.historial || []);
    modalEditar.classList.remove("hidden");
  } catch (e) {
    console.error(e);
    alert("No se pudo cargar la información: " + e.message);
  }
}

// ======= 12) Renderizar historial dentro modal =======
function renderHistorial(historial) {
  listHist.innerHTML = "";
  if (!historial.length) {
    listHist.innerHTML = `<p class="text-sm text-gray-500 italic">No hay comentarios previos.</p>`;
    return;
  }
  historial.forEach(({ id, usuario, fecha, descripcion }) => {
    const div = document.createElement("div");
    div.className =
      "bg-white p-4 border-l-4 border-orange-500 rounded-md shadow-sm";
    div.dataset.idstatus = id;
    div.innerHTML = `
      <div class="flex justify-between items-center mb-1">
        <div class="flex items-center space-x-2">
          <p class="text-sm font-semibold text-gray-800">${usuario}</p>
          <button type="button" class="btn-edit-hist text-xs text-blue-600">✏️</button>
          <button type="button" class="btn-del-hist text-xs text-red-600">🗑️</button>
        </div>
        <span class="text-xs text-gray-500">${fecha}</span>
      </div>
      <p class="text-sm text-gray-600 whitespace-pre-line">${descripcion}</p>
    `;
    listHist.appendChild(div);
  });
}

// ======= 13) Cerrar modal editar =======
function cerrarModal(event) {
  if (event.target === modalEditar || event.target.closest("#button-cancel")) {
    modalEditar.classList.add("hidden");
  }
}
// ======= ) Filtrado de tabla =======

function filtrarTabla(buscar, filtro) {
  const tbody = document.getElementById("tableBody");
  const rows = tbody.getElementsByTagName("tr");
  let found = false;

  for (let i = 0; i < rows.length; i++) {
    const cells = rows[i].getElementsByTagName("td");
    let showRow = false;

    switch (filtro) {
      case "id_cliente": // N° (idconsulta)
        showRow =
          cells[1] &&
          cells[1].textContent.toLowerCase().includes(buscar.toLowerCase());
        break;
      case "usuario": // Usuario
        showRow =
          cells[2] &&
          cells[2].textContent.toLowerCase().includes(buscar.toLowerCase());
        break;
      case "fecha_requerimiento": // Fecha Requerimiento
        showRow =
          cells[3] &&
          cells[3].textContent.toLowerCase().includes(buscar.toLowerCase());
        break;
      case "empresa": // Empresa
        showRow =
          cells[4] &&
          cells[4].textContent.toLowerCase().includes(buscar.toLowerCase());
        break;
      case "requerimiento": // Requerimiento (asunto)
        showRow =
          cells[5] &&
          cells[5].textContent.toLowerCase().includes(buscar.toLowerCase());
        break;
      case "derivado": // Origen
        showRow =
          cells[6] &&
          cells[6].textContent.toLowerCase().includes(buscar.toLowerCase());
        break;
      case "status": // Status
        showRow =
          cells[7] &&
          cells[7].textContent.toLowerCase().includes(buscar.toLowerCase());
        break;
      case "nombres": // Nombres
        showRow =
          cells[8] &&
          cells[8].textContent.toLowerCase().includes(buscar.toLowerCase());
        break;
      case "apellidos": // Apellidos
        showRow =
          cells[9] &&
          cells[9].textContent.toLowerCase().includes(buscar.toLowerCase());
        break;
      case "fecha_respuesta_cliente": // Fecha Respuesta Cliente
        showRow =
          cells[10] &&
          cells[10].textContent.toLowerCase().includes(buscar.toLowerCase());
        break;
      case "fecha_atencion": // Fecha Atención
        showRow =
          cells[11] &&
          cells[11].textContent.toLowerCase().includes(buscar.toLowerCase());
        break;
      case "email": // Email
        showRow =
          cells[12] &&
          cells[12].textContent.toLowerCase().includes(buscar.toLowerCase());
        break;
      case "direccion": // Dirección
        showRow =
          cells[13] &&
          cells[13].textContent.toLowerCase().includes(buscar.toLowerCase());
        break;
      case "telefono": // Teléfono
        showRow =
          cells[14] &&
          cells[14].textContent.toLowerCase().includes(buscar.toLowerCase());
        break;
      case "celular": // Celular
        showRow =
          cells[15] &&
          cells[15].textContent.toLowerCase().includes(buscar.toLowerCase());
        break;
      case "archivo": // archivo
        showRow =
          cells[16] &&
          cells[16].textContent.toLowerCase().includes(buscar.toLowerCase());
        break;
      case "url_pagina": // URL Página
        showRow =
          cells[17] &&
          cells[17].textContent.toLowerCase().includes(buscar.toLowerCase());
        break;
      default:
        showRow = false;
        break;
    }

    rows[i].style.display = showRow ? "" : "none";
    if (showRow) found = true;
  }

  if (!found) {
    Swal.fire({
      icon: "info",
      title: "Sin resultados",
      text: "No se encontraron registros que coincidan con la búsqueda.",
    });
  }
}
document.addEventListener("DOMContentLoaded", () => {
  document
    .getElementById("rowsPerPage")
    ?.addEventListener("change", ()=>updateRowsPerPage('rowsPerPage'));
  document
    .getElementById("btnActualizarResultado")
    ?.addEventListener("click", renderTable);
  renderTable();
  document
    .getElementById("button-search-filters")
    .addEventListener("click", function () {
      const searchValue = document.getElementById("texto-buscar").value.trim();
      const filterBy = document.getElementById("filtro-por").value;

      if (searchValue === "" || filterBy === "") {
        Swal.fire({
          icon: "warning",
          title: "Campos incompletos",
          text: "Por favor ingrese un término de búsqueda y seleccione un filtro.",
        });
        return;
      }

      filtrarTabla(searchValue, filterBy);
    });
});

// ======= Función para crear el modal dinámicamente =======
function createHistorialModal() {
  const modal = document.createElement("div");
  modal.id = "historialModal";
  modal.className =
    "fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 m-0 p-0"; // Add m-0 p-0 to remove margin/padding

  modal.innerHTML = `
  <div class="bg-white rounded-lg shadow-2xl w-full max-w-2xl relative max-h-[90vh] overflow-y-auto mt-0">      <!-- CABECERA -->
      <div class="bg-primary text-white rounded-t-lg px-6 py-4 flex items-center">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.657 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        <h1 class="text-lg font-semibold">Editar Consulta</h1>
      </div>

      <!-- BOTÓN CERRAR -->
      <button id="closeModalBtn" class="absolute top-4 right-4 text-white hover:text-gray-200">
        <svg class="w-6 h-6 m-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>

      <!-- CONTENIDO -->
      <div class="px-6 pt-4 pb-6">
        <div id="comentarios-status">
          <div class="mb-5">
            <h2 class="text-xl font-semibold text-gray-700 mb-2">Historial de Status - ID 
              <input type="text" id="modalIdConsulta"
                class="bg-transparent border-none text-xl font-semibold text-gray-700 w-[90px] pointer-events-none select-none"
                readonly>
            </h2>
            <textarea id="textAreaHist"
              class="w-full h-24 p-3 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-400 resize-y"
              placeholder="Escribe el nuevo historial..."></textarea>
            <button type="button" id="btnAgregarHistorial"
              class="mt-3 px-4 py-2 bg-primary text-white text-sm font-semibold rounded-md hover:bg-orange-600 transition">
              ➕ Agregar al historial
            </button>
          </div>

          <!-- LISTA DE HISTORIAL -->
          <div id="lista-historial-status" class="space-y-4 max-h-[50vh] pr-1">
            <!-- Aquí van las tarjetas -->
          </div>
        </div>
      </div>
    </div>
  `;

  return modal;
}

// ======= Configurar listeners del modal =======
function setupHistorialModalListeners(modal) {
  // Listener para el botón de cerrar
  const closeBtn = modal.querySelector("#closeModalBtn");
  if (closeBtn) {
    closeBtn.addEventListener("click", () => {
      modal.classList.add("hidden");
    });
  }

  // Listener para cerrar al hacer clic fuera del modal
  modal.addEventListener("click", (e) => {
    if (e.target === modal) {
      modal.classList.add("hidden");
    }
  });

  // Listener para el botón de agregar
  const btnAgregar = modal.querySelector("#btnAgregarHistorial");
  if (btnAgregar) {
    btnAgregar.addEventListener("click", agregarHistorial);
  }
}

// ======= 14) Handle abrir modal historial =======
async function handleVerStatus(event) {
  event.preventDefault();

  let modal = document.getElementById("historialModal");
  if (!modal) {
    modal = createHistorialModal();
    document.body.appendChild(modal);
    setupHistorialModalListeners(modal); // Configurar listeners después de crear
  }

  const idConsulta = event.target.dataset.id;
  const idInput = modal.querySelector("#modalIdConsulta");
  const listaHistorial = modal.querySelector("#lista-historial-status");

  if (!idInput || !listaHistorial) {
    console.error("Error: No se encontraron los elementos dentro del modal");
    return;
  }

  // Mostrar el ID en el modal
  idInput.value = idConsulta;
  listaHistorial.innerHTML =
    '<p class="text-center py-4">Cargando historial...</p>';

  try {
    // Cargar historial real desde el servidor
    const response = await fetch(
      `${base}controller/requerimientos/cargar.php?id=${idConsulta}`
    );
    const result = await response.json();

    if (!result.success) {
      throw new Error(result.message || "Error al cargar el historial");
    }

    // Mostrar el historial
    renderHistorialReal(result.data || [], listaHistorial);
  } catch (error) {
    console.error("Error al cargar el historial:", error);
    listaHistorial.innerHTML = `
      <p class="text-red-500 text-center py-4">
        Error al cargar el historial: ${error.message}
      </p>
    `;
  }

  // Mostrar el modal
  modal.classList.remove("hidden");
}

// Función para renderizar el historial real
function renderHistorialReal(historial, container) {
  container.innerHTML = "";

  if (!historial.length) {
    container.innerHTML =
      '<p class="text-sm text-gray-500 italic text-center py-4">No hay historial registrado</p>';
    return;
  }

  historial.forEach((item) => {
    const div = document.createElement("div");
    div.className = "border-b pb-3";

    // Prioridad para mostrar el nombre:
    // 1. nombre_completo (si existe)
    // 2. nombres + apellidos
    // 3. usuario
    // 4. "Sistema" como fallback
    let nombreMostrar = "Sistema";

    if (item.nombre_completo) {
      nombreMostrar = item.nombre_completo;
    } else if (item.nombres && item.apellidos) {
      nombreMostrar = `${item.nombres} ${item.apellidos}`;
    } else if (item.usuario) {
      nombreMostrar = item.usuario;
    }

    div.innerHTML = `
      <div class="font-medium text-gray-800">${nombreMostrar}</div>
      <div class="text-xs text-gray-500">${formatFecha(item.fechaingreso)}</div>
      <div class="text-sm mt-1">${item.status || ""}</div>
    `;
    container.appendChild(div);
  });
}
// Función para formatear fecha
function formatFecha(fecha) {
  if (!fecha) return "";
  const date = new Date(fecha);
  return date.toLocaleString("es-ES", {
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
}

// ======= 15) Función para agregar historial =======
async function agregarHistorial() {
  const modal = document.getElementById("historialModal");
  if (!modal) return;

  const idConsulta = modal.querySelector("#modalIdConsulta").value;
  const comentario = modal.querySelector("#textAreaHist").value.trim();
  const btnAgregar = modal.querySelector("#btnAgregarHistorial");

  if (!comentario) {
    Swal.fire("Error", "Por favor escribe un comentario", "warning");
    return;
  }

  // Deshabilitar botón durante la petición
  btnAgregar.disabled = true;
  btnAgregar.innerHTML = "Guardando...";

  try {
    const response = await fetch(base+"controller/requerimientos/guardar.php",
      {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          idconsulta: idConsulta,
          comentario: comentario,
        }),
      }
    );

    const result = await response.json();

    if (!response.ok) {
      throw new Error(result.message || "Error en la petición");
    }

    if (result.success) {
      modal.querySelector("#textAreaHist").value = "";
      Swal.fire("Éxito", result.message, "success");

      // Actualizar lista de historial
      const link = document.querySelector(
        `.ver-status-link[data-id="${idConsulta}"]`
      );
      if (link) {
        await handleVerStatus({ target: link, preventDefault: () => {} });
      }
      window.renderTable();
    } else {
      throw new Error(result.message);
    }
  } catch (error) {
    console.error("Error:", error);
    Swal.fire("Error", error.message, "error");
  } finally {
    btnAgregar.disabled = false;
    btnAgregar.innerHTML = "➕ Agregar al historial";
  }
}

// Inicialización cuando el DOM esté listo
document.addEventListener("DOMContentLoaded", () => {
  // Configurar listeners para el modal de historial si ya existe
  const modal = document.getElementById("historialModal");
  if (modal) {
    setupHistorialModalListeners(modal);
  }
});

//limpiar el actualizar uu
function limpiarcamposac() {
  document.getElementById("filtro-por").value = "";
  document.getElementById("texto-buscar").value = "";
}
//fehcas con formato completo wassa
function fechaLarga(fecha) {
  if (!fecha) return "";
  //si tiene hora la omite
  const soloFecha = fecha.split(" ")[0];
  //lo convertimos si es q tiene el formato tipo --/--/----
  const partes = soloFecha.includes("-")
    ? soloFecha.split("-")
    : soloFecha.split("/");

  let dia, mes, anio;
  if (soloFecha.includes("-")) {
    [anio, mes, dia] = partes;
  } else {
    [dia, mes, anio] = partes;
  }
  const meses = [
    "enero",
    "febrero",
    "marzo",
    "abril",
    "mayo",
    "junio",
    "julio",
    "agosto",
    "septiembre",
    "octubre",
    "noviembre",
    "diciembre",
  ];
  return `${dia} de ${meses[parseInt(mes, 10) - 1]} de ${anio}`;
}
