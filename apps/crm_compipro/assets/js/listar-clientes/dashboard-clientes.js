import { mostrarTx, switchModal } from "../utils.js";
import { paginacion, updateRowsPerPage } from "../requerimientos/paginacion.js";

document.addEventListener("DOMContentLoaded", () => {
  const perPage = document.getElementById("rowsPerPage");
  const spanReg = document.getElementById("cantidad-registros");
  const tablaBody = document.querySelector(".tabla-mini tbody");
  const contador = document.getElementById("contador");
  const pagNav = document.querySelector('nav[aria-label="Pagination"]');

  const preloader = document.getElementById("preloader");

  function cambiarRegistrosPorPagina(value) {
    let url = crearURLConFiltros({ limit: value, page: 1 });
    renderTable(url);
  }

  // FILTRO UNICAMENTE POR NÚMERO DE CLIENTE
  const inputNum = document.getElementById("filtro-cliente");
  if (inputNum && tablaBody) {
    inputNum.addEventListener("keypress", (e) => {
      if (e.key === "Enter") {
        e.preventDefault();
        const num = inputNum.value.trim();
        const params = new URLSearchParams({
          action: "filter_num_cliente",
          num,
          page: 1,
          limit: perPage.value,
        });
        const spinner = document.getElementById("icon-spinner");
        const iconX = document.getElementById("icon-x");
        iconX.classList.add("hidden");
        spinner.classList.remove("hidden");
        if (!num || num.length == 1 || /^-?\d+$/.test(num) === false) {
          spinner.classList.add("hidden");
          iconX.classList.remove("hidden");
          return;
        }
        // Avisar que se está realizando una búsqueda
        fetch(`${base}controller/cliente.php?${params}`)
          .then((r) => r.json())
          .then((json) => {
            if (!json.success)
              throw new Error(json.message || "Error servidor");
            // OCULTAR TODAS LAS FILAS
            Array.from(tablaBody.rows).forEach((r) => {
              r.style.display = "none";
            });
            // MOSTRAR FILAS QUE COINCIDAN
            json.body = json.body || "";
            if (json.body) {
              const filas = json.body.split("</tr>");
              filas.forEach((fila) => {
                if (fila.trim()) {
                  const tr = document.createElement("tr");
                  tr.innerHTML = fila;
                  tr.dataset.filter = "true";
                  tablaBody.appendChild(tr);
                }
              });
            }
            if (json.paginationHtml) pagNav.innerHTML = json.paginationHtml;
            spinner.classList.add("hidden");
            iconX.classList.remove("hidden");
            window.setFunctions();
          })
          .catch(console.error);
      }
    });
  }

// FILTRAR SEGUN ELECION DE USUARIO: IDCLIENTE,EMPRESA,RAZON SOCIAL,ETC
const inputBuscar = document.getElementById("texto-buscar");
const filtroPor = document.getElementById("filtro-por");

if (inputBuscar && filtroPor && tablaBody) {
  inputBuscar.addEventListener("keypress", (e) => {
    if (e.key === "Enter") {
      e.preventDefault();

      const campoSeleccionado = filtroPor.value;
      const valor = inputBuscar.value.trim();

      // Mapeo de valores del select a campos reales en BD
      const camposMapeados = {
        'num-cliente': 'idcliente',
        'empresa': 'empresa',
        'razon_social': 'razon',
        'nombres': 'nombres',
        'apellidos': 'apellidos',
        'usuarios': 'usuario',
        'rubro': 'rubro',
        'ruc': 'ruc',
        'telefono': 'telefono',
        'celular': 'celular',
        'fecha_atencion': 'fatencion',
        'direccion_cliente': 'direccion',
        'direccion_empresa': 'direccion2',
        'emails': 'email',
        'web': 'web'
      };

      const campo = camposMapeados[campoSeleccionado] || campoSeleccionado;
      if (!valor) return;

      const params = new URLSearchParams({
        action: campo === 'todos' ? 'buscar_en_todos' : 'filter_por_campo',
        campo,
        valor,
        page: 1,
        limit: perPage.value,
      });


      fetch(`${base}controller/cliente.php?${params}`)
        .then((r) => r.json())
        .then((json) => {
          if (!json.success) throw new Error(json.message || "Error servidor");

          // Ocultar todas las filas actuales
          Array.from(tablaBody.rows).forEach((r) => (r.style.display = "none"));

          // Insertar nuevas filas
          json.body = json.body || "";
          if (json.body) {
            const filas = json.body.split("</tr>");
            filas.forEach((fila) => {
              if (fila.trim()) {
                const tr = document.createElement("tr");
                tr.innerHTML = fila;
                tr.dataset.filter = "true";
                tablaBody.appendChild(tr);
              }
            });
          }

          if (json.paginationHtml) pagNav.innerHTML = json.paginationHtml;
          window.setFunctions(); // si tienes funciones adicionales por fila
        })
        .catch(console.error);
    }
  });
}


//DUPLICAR CLIENTE DESDE EL MODAL
const modal   = document.getElementById("modalCliente");
const baseUrl = (typeof base === "string" ? base : "/");

  // Utilidad: obtener idcliente desde el modal (prioriza input del formulario)
  function getClientIdFromModal() {
    if (!modal) return null;

    // Hay varios inputs name="idcliente" en tus tabs. Tomemos el primero con valor numérico.
    const inputs = modal.querySelectorAll('input[name="idcliente"]');
    for (const el of inputs) {
      const val = parseInt((el.value || "").trim(), 10);
      if (!Number.isNaN(val) && val > 0) return val;
    }
    return null;
  }

  // Delegación de clic para el botón dentro del modal
  modal?.addEventListener("click", (e) => {
    const btn = e.target.closest("#duplicateClientButton");
    if (!btn) return;

    e.preventDefault();

    // 1) Intentar desde input del modal
    let clientId = getClientIdFromModal();

    // 2) Fallback: data-client-id del botón
    if (!clientId) {
      const dataId = parseInt(btn.dataset.clientId || "", 10);
      if (!Number.isNaN(dataId) && dataId > 0) clientId = dataId;
    }

    if (!clientId) {
      Swal.fire("Error", "No se pudo obtener el ID del cliente.", "error");
      return;
    }

    // Confirmación + loader en el mismo popup (sin custom CSS)
    Swal.fire({
      title: "¿Duplicar Cliente?",
      text: "Se creará una copia de este cliente.",
      icon: "question",
      showCancelButton: true,
      confirmButtonText: "Duplicar",
      cancelButtonText: "Cancelar",
      buttonsStyling: true,
      confirmButtonColor: "#EB690B",
      cancelButtonColor: "#6c757d",
      reverseButtons: true,
      allowOutsideClick: () => !Swal.isLoading(),
      allowEscapeKey: () => !Swal.isLoading(),
      preConfirm: async () =>  {
        try {
          Swal.showLoading();
          const resp = await fetch(`${baseUrl}controller/cliente.php?action=duplicate`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            // credentials: "include", // descomenta si tu sesión lo requiere
            body: JSON.stringify({ idcliente: clientId })
          });

          // Intenta parsear JSON siempre
          const data = await resp.json().catch(() => ({}));

          if (!resp.ok || !data.success) {
            throw new Error(data.message || `HTTP ${resp.status}`);
          }

          return data; // pasa al .then final

        } catch (err) {
          Swal.showValidationMessage(err?.message || "Error de conexión");
          return false;
        }
      }
    }).then((r) => {
      if (!r.isConfirmed || !r.value) return;

      Swal.fire({
        icon: "success",
        title: "Cliente duplicado", 
        text: `Nuevo ID: ${r.value.idcliente}`,
        position: "center",
        showConfirmButton: true
      }).then(() => {
        if (modal) {
          modal.classList.add('hidden');
          document.body.style.overflow = '';
        }
        window.reloadClientesTable();
      });
    });
  });


// LIMPIAR INPUTS
  window.clearInputBP = () => {
    const i = document.getElementById("texto-buscar");
    if (i) {
      i.value = "";
      i.focus();
      // MOSTRAR TODAS LAS FILAS
      Array.from(tablaBody.rows).forEach((r) => {
        if (r.style.display == "none") {
          r.style.display = "";
        } else if (r.dataset.filter == "true") {
          tablaBody.removeChild(r);
        }
      });
    }

    /*if (i) {
      const text = i.value;
      i.focus();
      if(!text) return;
      i.value = "";
      filtrarLocal();
    }*/
    // Array.from(tablaBody.rows).forEach((r) => {
    //   if (r.style.display == "none") {
    //     r.style.display = "";
    //   }
    // });
  };
  window.clearInputNC = () => {
    const i = document.getElementById("filtro-cliente");
    if (i) {
      i.value = "";
      i.focus();
      // MOSTRAR TODAS LAS FILAS
      Array.from(tablaBody.rows).forEach((r) => {
        if (r.style.display == "none") {
          r.style.display = "";
        } else if (r.dataset.filter == "true") {
          tablaBody.removeChild(r);
        }
      });
    }
  };

  // FILTRO POR CAMPO LOCAL
  /*const selLocal = document.getElementById("filtro-por");
  const txtLocal = document.getElementById("texto-buscar");*/
  const tipoClienteSelect = document.getElementById("filtro-tipo-cliente");
  const statusSelect = document.getElementById("filtro-status");
  const origenSelect = document.getElementById("filtro-origen");

  const colIndex = {
    todos: null,
    "num-cliente": 1,
    usuarios: 2,
    fecha_atencion: 3,
    empresa: 4,
    razon_social: 5,
    rubro: 6,
    tipo_cliente: 7,
    origen: 8,
    estado_atencion: 10,
    nombres: 11,
    apellidos: 12,
    cargo: 13,
    telefono: 14,
    celular: 14,
    ruc: 15,
    emails: 16,
    web: 17,
    direccion_cliente: 18,
    direccion_empresa: 19,
    obs_direccion: 20,
    referencia: 21,
    distrito: 22,
    ciudad: 23,
    cumpleanios: 24,
    aniversario: 25,
  };
  //ENTES SECION SE DEBE AGREGAR LO DE FILTRO LOCAL

  const updateButton = document.getElementById("btnActualizarResultado");
  if (updateButton) {
    updateButton.addEventListener("click", () => {
      document.getElementById("filtro-origen").value = "";
      document.getElementById("filtro-cliente").value = "";
      document.getElementById("filtro-status").value = "";
      document.getElementById("filtro-tipo-cliente").value = "";
      document.getElementById("texto-buscar").value = "";
      document.getElementById("filtro-por").selectedIndex = 0;
      document.getElementById("fecha-rango").value = "";
      document.getElementById("fecha-desde").textContent = "Desde:";
      document.getElementById("fecha-hasta").textContent = "Hasta:";
      const radios = document.getElementsByName("tipoFecha");
      radios.forEach((radio) => {
        radio.checked = false;
      });
      const url = crearURLConFiltros({ page: 1 }, true);
      renderTable(url);
    });
  }

  // FLATPICKR RANGO DE FECHAS
  const input = document.getElementById("fecha-rango");
  const spanDesde = document.getElementById("fecha-desde");
  const spanHasta = document.getElementById("fecha-hasta");
  const desdeParam = new URL(window.location.href).searchParams.get("desde");
  const hastaParam = new URL(window.location.href).searchParams.get("hasta");

  const defaultDates = [];
  if (desdeParam) defaultDates.push(new Date(desdeParam + "T00:00:00"));
  if (hastaParam) defaultDates.push(new Date(hastaParam + "T23:59:59"));

  if (input) {
    flatpickr(input, {
      mode: "range",
      dateFormat: "d/m/Y",
      defaultDate: defaultDates.length === 2 ? defaultDates : null,
      locale: { rangeSeparator: " al ", firstDayOfWeek: 1 },
      onChange: (selectedDates, dateStr, instance) => {
        const u = crearURLConFiltros();
        const tipoFecha = document.querySelector(
          'input[name="tipoFecha"]:checked'
        );

        if (selectedDates.length === 2) {
          const [d, h] = selectedDates;
          u.searchParams.set("desde", formatDateForQuery(d));
          u.searchParams.set("hasta", formatDateForQuery(h));
          u.searchParams.set("page", "1");
          spanDesde.textContent = "Desde: " + formatDateForQuery(d);
          spanHasta.textContent = "Hasta: " + formatDateForQuery(h);
          if (tipoFecha) {
            u.searchParams.set(
              "fecha_aviso",
              tipoFecha.value == "aviso" ? "1" : "0"
            );
            renderTable(u); // ✅
          }
        }
      },
      onReady: function (selectedDates, dateStr, instance) {
        if (defaultDates.length === 2) {
          instance.input.value =
            formatDate(defaultDates[0]) + " al " + formatDate(defaultDates[1]);
          spanDesde.textContent =
            "Desde: " + formatDateForQuery(defaultDates[0]);
          spanHasta.textContent =
            "Hasta: " + formatDateForQuery(defaultDates[1]);
        }
      },
    });
  }

  const fechaAvisoParam = new URL(window.location.href).searchParams.get(
    "fecha_aviso"
  );
  // Tipo de fecha (aviso o atención)
  document.querySelectorAll('input[name="tipoFecha"]').forEach((radio) => {
    radio.checked =
      (fechaAvisoParam != null &&
        radio.value == "aviso" &&
        fechaAvisoParam == "1") ||
      (radio.value == "atencion" && fechaAvisoParam == "0");
    radio.addEventListener("change", function () {
      const tipo = this.value;
      const u = crearURLConFiltros({
        fecha_aviso: tipo === "aviso" ? "1" : "0",
      });

      const fechaDesde = spanDesde.textContent.trim() !== "Desde:";
      const fechaHasta = spanHasta.textContent.trim() !== "Hasta:";

      if (fechaDesde || fechaHasta) {
        u.searchParams.set(
          "desde",
          spanDesde.textContent.replace("Desde: ", "")
        );
        u.searchParams.set(
          "hasta",
          spanHasta.textContent.replace("Hasta: ", "")
        );
        renderTable(u); // ✅
      }
    });
  });

  function formatDate(date) {
    return date.toLocaleDateString("es-PE", {
      day: "2-digit",
      month: "2-digit",
      year: "numeric",
    });
  }

  function formatDateForQuery(date) {
    return date.toISOString().split("T")[0];
  }

  function crearURLConFiltros(extra = {}, deleteBefore = false) {
    const currentURL = new URL(window.location.href);
    const searchParams = new URLSearchParams(currentURL.search);

    if (deleteBefore) {
      searchParams.delete("hasta");
      searchParams.delete("fecha_aviso");
      searchParams.delete("desde");
    }
    // Agregar extra o sobrescribir
    Object.entries(extra).forEach(([key, value]) => {
      if (value !== null && value !== undefined) {
        searchParams.set(key, value);
      }
    });
    const limitActual = localStorage.getItem("limit");
    if (limitActual) searchParams.set("limit", limitActual);

    // Actualizar URL sin recargar
    const newURL = new URL(window.location.origin + window.location.pathname);
    newURL.search = searchParams.toString();
    return newURL;
  }

  function generarAccionesHTML(idcliente, propio) {
    const data_propio = propio ? "si" : "no";

    const editarBtn = `
      <button class="p-0 m-0 bg-transparent border-none outline-none shadow-none hover:bg-gray-100" title="Editar">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 m-1 text-blue-600 hover:text-blue-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
        </svg>
      </button>`;

    const eliminarBtn = `
      <button class="p-0 m-0 bg-transparent border-none outline-none shadow-none hover:bg-gray-100" title="Eliminar">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 m-1 text-red-600 hover:text-red-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
        </svg>
      </button>`;

    const protegidoBtn = `
      <button class="btn-protegido p-0 m-0 bg-transparent border-none outline-none hover:bg-gray-100" title="Protegido">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 m-1 text-purple-600 hover:text-red-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M16 8V6a4 4 0 00-8 0v2m-2 0h12a2 2 0 012 2v8a2 2 0 01-2 2H6a2 2 0 01-2-2v-8a2 2 0 012-2z" />
        </svg>
      </button>`;

    return `
      <div data-id="${idcliente}" data-propio="${data_propio}" class="flex flex-row space-x-2">
        ${editarBtn}
        ${propio ? eliminarBtn : protegidoBtn}
      </div>`;
  }
  function generarTelefonos(datos) {
    const campos = ["telefono", "celular", "celular2", "celular3", "celular4"];
    return campos
      .map((campo) => datos[campo]?.trim() || "")
      .filter((valor) => valor !== "")
      .join("\n");
  }
  function generarEmails(emailRaw) {
    return emailRaw ? emailRaw.replace(/, /g, "\n") : "";
  }
  function renderTable(url) {
    //Actualizar URL del navegador sin recargar
    window.history.pushState({}, "", url.toString());
    switchModal(preloader, true);
    // Sincronizar con Notion
    // fetch(`${base}controller/cliente/notion/sync_from_notion.php`)
    //   .then((r) => r.json())
    //   .then((json) => {
    //     if (!json.success) {
    //       console.error("Error en la sincronización:", json.message);
    //       switchModal(preloader, false);
    //       Swal.fire({icon: "error", title: "Error", text: json.message, position: "top-end", toast: true, showConfirmButton: false, timer: 3000, timerProgressBar: true,
    //       });
    //       return;
    //     }

    //     // Mostrar mensaje de sincronización exitosa con SweetAlert2
    //     Swal.fire({
    //       icon: "success", title: "Sincronización completada",
    //       text: "Sincronización con Notion completada correctamente.",
    //       position: "top-end", // Esquina superior derecha
    //       toast: true, // Modo toast (compacto)
    //       showConfirmButton: false, // Sin botón de confirmación
    //       timer: 3000, // Desaparece en 3 segundos
    //       timerProgressBar: true, // Barra de progreso
    //     });

    //     // Obtener la lista de clientes
    //     return fetch(
    //       `${base}controller/cliente/obtener_lista.php?${url.searchParams.toString()}`
    //     );
    //   })
      fetch(`${base}controller/cliente/obtener_lista.php?${url.searchParams.toString()}`)
      .then(response => response.text())
      .then(text => {
        //console.log('Respuesta cruda del servidor:', text); // ⬅ Aquí ves el HTML o el error
        try {
          const json = JSON.parse(text);
        if (!json || !Array.isArray(json.clientes)) return;

        tablaBody.innerHTML = "";

        json.clientes.forEach((cliente) => {
          const accionesHTML = generarAccionesHTML(
            cliente.idcliente,
            cliente.propio == "1"
          );

          const estado_cliente = cliente.estado_cliente ?? "";
          const max_length_estado_cliente = 15;
          const isLarge = estado_cliente.length > max_length_estado_cliente;
          const estado_truncado = isLarge
            ? estado_cliente.substring(0, max_length_estado_cliente) + "..."
            : estado_cliente;
          const spanStatus =
            '<br><span class="ver-status text-blue-600 cursor-pointer hover:underline">Ver status</span>';
          const estado_cliente_con_status = estado_truncado + spanStatus;

          const telefonos = generarTelefonos(cliente);
          const emails = generarEmails(cliente.email);
          const valores = [
            accionesHTML,
            cliente.idcliente || "",
            cliente.usuario || "",
            cliente.fatencion || "",
            cliente.empresa || "",
            cliente.razon || "",
            cliente.rubro || "",
            cliente.tipo_cliente || "",
            cliente.origen_nombre || "",
            estado_cliente_con_status,
            cliente.estado_atencion || "",
            cliente.nombres || "",
            cliente.apellidos || "",
            cliente.cargo || "",
            telefonos,
            cliente.ruc || "",
            emails,
            cliente.web || "",
            cliente.direccion || "",
            cliente.direccion2 || "",
            cliente.obsdireccion || "",
            cliente.referencia || "",
            cliente.distrito || "",
            cliente.ciudad || "",
            cliente.cumpleanios || "",
            cliente.aniversario || "",
          ];
          const tr = document.createElement("tr");
          tr.innerHTML = mostrarTx(valores, 1);
          tablaBody.appendChild(tr);
        });
        if (json.clientes.length > 0) {
          paginacion.totalPages = json.totalPaginas;
          paginacion.updatePaginationInfo(json.totalClientes);
          paginacion.renderPaginationControls();
          if (contador) {
            contador.textContent = `${json.clientesGlobal} Clientes`;
          }
          if (json.paginationHtml) {
            pagNav.innerHTML = json.paginationHtml;
          }
          window.setFunctions?.();
          filtrarLocal();
        }
        } catch (e) {
          console.error('Error al parsear JSON:', e);
          console.log('Respuesta que no es JSON:', text);
        }
      })
      .catch((err) => {
        console.error("Error obteniendo datos:", err);
        Swal.fire({
          icon: "error",
          title: "Error",
          text: "Error al cargar los datos de clientes.",
          position: "top-end",
          toast: true,
          showConfirmButton: false,
          timer: 3000,
          timerProgressBar: true,
        });
      })
      .finally(() => switchModal(preloader, false));
  }

  paginacion.renderFunction = renderTable;
  const limitParam =
    new URL(window.location.href).searchParams.get("limit") ?? 10;
  paginacion.rowsPerPage = limitParam;
  const pageParam = new URL(window.location.href).searchParams.get("page") ?? 1;
  paginacion.currentPage = pageParam;
  const initialURL = paginacion.getURLConFiltros();
  renderTable(initialURL);
  // Guardar y aplicar límite de registros por página
  perPage?.addEventListener("change", (e) => {
    spanReg.textContent = perPage.value;
    const valor = e.target.value;
    localStorage.setItem("limit", valor);
    updateRowsPerPage(perPage.id);
  });
  perPage.value = limitParam;
  spanReg.textContent = limitParam;
  // PROBAR
 //setInterval(() => renderTable(paginacion.getURLConFiltros()), 300000); // Cada 5 minutos
  window.reloadClientesTable = function () {
    const url = paginacion.getURLConFiltros();
    renderTable(url);
  };

});