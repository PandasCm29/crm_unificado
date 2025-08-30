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

  // FILTRO GLOBAL: Número de Cliente
  // const inputNum = document.getElementById("filtro-cliente");
  // if (inputNum && tablaBody) {
  //   inputNum.addEventListener("keypress", (e) => {
  //     if (e.key === "Enter") {
  //       e.preventDefault();
  //       const num = inputNum.value.trim();
  //       const params = new URLSearchParams({
  //         action: "filter_num_cliente",
  //         num,
  //         page: 1,
  //         limit: perPage.value,
  //       });
  //       const spinner = document.getElementById("icon-spinner");
  //       const iconX = document.getElementById("icon-x");
  //       iconX.classList.add("hidden");
  //       spinner.classList.remove("hidden");
  //       if (!num || num.length == 1 || /^-?\d+$/.test(num) === false) {
  //         spinner.classList.add("hidden");
  //         iconX.classList.remove("hidden");
  //         return;
  //       }
  //       // Avisar que se está realizando una búsqueda
  //       fetch(`${base}controller/cliente.php?${params}`)
  //         .then((r) => r.json())
  //         .then((json) => {
  //           if (!json.success)
  //             throw new Error(json.message || "Error servidor");
  //           // OCULTAR TODAS LAS FILAS
  //           Array.from(tablaBody.rows).forEach((r) => {
  //             r.style.display = "none";
  //           });
  //           // MOSTRAR FILAS QUE COINCIDAN
  //           json.body = json.body || "";
  //           if (json.body) {
  //             const filas = json.body.split("</tr>");
  //             filas.forEach((fila) => {
  //               if (fila.trim()) {
  //                 const tr = document.createElement("tr");
  //                 tr.innerHTML = fila;
  //                 tr.dataset.filter = "true";
  //                 tablaBody.appendChild(tr);
  //               }
  //             });
  //           }
  //           if (json.paginationHtml) pagNav.innerHTML = json.paginationHtml;
  //           spinner.classList.add("hidden");
  //           iconX.classList.remove("hidden");
  //           window.setFunctions(false);
  //         })
  //         .catch(console.error);
  //     }
  //   });
  // }

  // LIMPIAR INPUTS
  window.clearInputBP = () => {
    const i = document.getElementById("texto-buscar");
    if (i) {
      const text = i.value;
      i.focus();
      if (!text) return;
      i.value = "";
      filtrarLocal();
    }
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
  const selLocal = document.getElementById("filtro-por");
  const txtLocal = document.getElementById("texto-buscar");
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
  const filtrarLocal = () => {
    // Validar entradas
    if (!tablaBody || !txtLocal || !selLocal) {
      console.error("Elementos necesarios no están definidos");
      return;
    }
    const f = txtLocal.value.toLowerCase().trim();
    const indexTipoCliente = colIndex["tipo_cliente"];
    const indexOrigen = colIndex["origen"];
    const indexStatusAtencion = colIndex["estado_atencion"];
    const valueTipoCliente = tipoClienteSelect.value;
    const valueOrigen = origenSelect.value;
    const valueStatusAtencion = statusSelect.value;
    if (!f && !valueOrigen && !valueStatusAtencion) {
      // Si no hay texto de búsqueda, mostrar todas las filas y limpiar resaltado
      Array.from(tablaBody.rows).forEach((row, counter) => {
        Array.from(row.cells).forEach((cell) => {
          cell.style.backgroundColor = ""; // Limpiar fondo
        });
        row.style.display = ""; // Mostrar todas las filas
      });
      return;
    }
    const idx = colIndex[selLocal.value];

    Array.from(tablaBody.rows).forEach((r, counter) => {
      Array.from(r.cells).forEach((cell) => {
        cell.style.backgroundColor = ""; // Restablecer fondo
      });
      const isTipoCliente =
        valueTipoCliente &&
        valueTipoCliente == r.cells[indexTipoCliente].textContent;
      const isOrigen =
        valueOrigen && valueOrigen == r.cells[indexOrigen].textContent;
      const isStatusAtencion =
        valueStatusAtencion &&
        valueStatusAtencion == r.cells[indexStatusAtencion].textContent;
      let evaluacion = isTipoCliente || isOrigen || isStatusAtencion;
      evaluacion =
        evaluacion ||
        valueTipoCliente + valueOrigen + valueStatusAtencion == "";
      if (idx == null) {
        let evalCols = false;
        Array.from(r.cells).forEach((cell) => {
          if (cell.textContent.toLowerCase().includes(f)) {
            evalCols = true; // Encontró coincidencia, marcar para mostrar la fila
            cell.style.backgroundColor = f ? "#FFFF00" : "";
          }
        });
        evaluacion = evaluacion && evalCols;
      } else {
        const c = (r.cells[idx]?.textContent || "").toLowerCase();
        const inText = c.includes(f);
        evaluacion = evaluacion && inText;
      }
      r.style.display = evaluacion ? "" : "none";
    });
  };
  // FILTRAR POR
  const filtrarElementos = () => {
    const limitParam =
      new URL(window.location.href).searchParams.get("limit") ?? 10;
    paginacion.rowsPerPage = limitParam;
    const pageParam = 1;
    paginacion.setCurrentPage(pageParam);
    const url = paginacion.getURLConFiltros({ page: pageParam });
    renderTable(url);
  }
  txtLocal?.addEventListener("keypress", (e) => {
    if (e.key === "Enter") {
      e.preventDefault();
      filtrarElementos();
    }
  });
  // selLocal?.addEventListener("change", filtrarLocal);
  // SELECTS DE FILTROS POR TIPO DE CLIENTE, STATUS Y ORIGEN
  tipoClienteSelect?.addEventListener("change", filtrarElementos);
  statusSelect?.addEventListener("change", filtrarElementos);
  origenSelect?.addEventListener("change", filtrarElementos);

  const searchButton = document.getElementById("button-search");
  if (searchButton) {
    searchButton.addEventListener("click", () => {
      const url = crearURLConFiltros({ page: 1 });
      renderTable(url); // ✅
    });
  }

  const checkBoxFPF = document.querySelectorAll('input[name="tipoFecha"]');
  const updateButton = document.getElementById("btnActualizarResultado");
  if (updateButton) {
    const texto = document.getElementById("cronometroTexto");
  
    // Al cargar la página, revisa si aún está dentro del tiempo de espera
    const proxima = localStorage.getItem("proximaActivacion");
    if (proxima && Date.now() < parseInt(proxima)) {
      let t = Math.floor((parseInt(proxima) - Date.now()) / 1000);
      updateButton.disabled = true;
      updateButton.classList.remove("bg-blue-600");
      updateButton.classList.add("bg-gray-400");
      texto.classList.remove("hidden");
  
      const timer = setInterval(() => {
        let m = Math.floor(t / 60);
        let s = t % 60;
        texto.textContent = `🕐 Disponible en ${m}:${s.toString().padStart(2, '0')}`;
        t--;
        if (t < 0) {
          clearInterval(timer);
          updateButton.disabled = false;
          updateButton.classList.remove("bg-gray-400");
          updateButton.classList.add("bg-blue-600");
          texto.classList.add("hidden");
          localStorage.removeItem("proximaActivacion");
        }
      }, 1000);
    }
  
    async function syncClientesToNotion(clientes) {
      try {
        const response = await fetch(`${base}controller/cliente/notion/sync_to_notion_batch.php`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ clientes })
        });
  
        if (!response.ok) {
          throw new Error('Error al sincronizar con Notion.');
        }
  
        const result = await response.json();
        return result;
      } catch (err) {
        console.error('Error al sincronizar con Notion:', err);
        return {success:false, mensaje: err};
      }
    }
  
    updateButton.addEventListener("click", async () => {
      // Establecer nueva cuenta regresiva y guardar en localStorage
      let t = 300;
      const proximaActivacion = Date.now() + t * 1000;
      localStorage.setItem("proximaActivacion", proximaActivacion);
  
      updateButton.disabled = true;
      updateButton.classList.remove("bg-blue-600");
      updateButton.classList.add("bg-gray-400");
      texto.classList.remove("hidden");
  
      const timer = setInterval(() => {
        let m = Math.floor(t / 60);
        let s = t % 60;
        texto.textContent = `🕐 Disponible en ${m}:${s.toString().padStart(2, '0')}`;
        t--;
        if (t < 0) {
          clearInterval(timer);
          updateButton.disabled = false;
          updateButton.classList.remove("bg-gray-400");
          updateButton.classList.add("bg-blue-600");
          texto.classList.add("hidden");
          localStorage.removeItem("proximaActivacion");
        }
      }, 1000);
  
      // ✅ Limpiar filtros
      document.getElementById("filtro-origen").value = "";
      document.getElementById("filtro-status").value = "";
      document.getElementById("filtro-tipo-cliente").value = "";
      document.getElementById("texto-buscar").value = "";
      document.getElementById("filtro-por").selectedIndex = 0;
      document.getElementById("fecha-rango").value = "";
      document.getElementById("fecha-desde").textContent = "Desde:";
      document.getElementById("fecha-hasta").textContent = "Hasta:";
      checkBoxFPF.forEach((radio) => {
        radio.checked = false;
      });
  
      switchModal(preloader, true);
      const url = crearURLConFiltros({ page: 1 }, true);
  
      // Notion -> DB
      try {
        await fetch(`${base}controller/cliente/notion/sync_from_notion.php`);
      } catch (err) {
        console.warn('Error en chequeo inicial, continuando...', err);
      }
  
      // DB -> Notion
      const response = await fetch(`${base}controller/cliente/obtener.php?sincro=true`);
      if (!response.ok) {
        throw new Error('Error al obtener lista de clientes.');
      }
  
      const clientes = await response.json();
      const syncResult = await syncClientesToNotion(clientes);
      renderTable(url);
      if (syncResult.errores && syncResult.errores.length > 0) {
        // Mostrar errores en una lista
        const listaErrores = syncResult.errores.map(err => 
          `ID: ${err.id} - ${err.error}`
        ).join("<br>");

        Swal.fire({
          icon: 'warning',
          title: syncResult.mensaje,
          html: listaErrores,
          confirmButtonText: 'Entendido'
        });
      } else {
        Swal.fire({
          toast: true,
          position: "top-end",
          icon: "success",
          title: syncResult.mensaje,
          showConfirmButton: false,
          timer: 2000,
          timerProgressBar: true,
        });
      }
  
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
            renderTable(u); 
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
  checkBoxFPF.forEach((radio) => {
    radio.checked =
      (fechaAvisoParam != null &&
        radio.value == "aviso" &&
        fechaAvisoParam == "1") ||
      (radio.value == "atencion" && fechaAvisoParam == "0");
    radio.addEventListener("change", function (e) {
      const checked = e.target.checked;
      const tipo = this.value;
      const u = crearURLConFiltros({
        fecha_aviso: tipo === "aviso" ? "1" : "0",
      });

      const fechaDesde = spanDesde.textContent.trim() !== "Desde:";
      const fechaHasta = spanHasta.textContent.trim() !== "Hasta:";

      if (checked && fechaDesde && fechaHasta) {
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

  function renderRow(
    cliente,
    tablaBody,
    replaceIndex = null,
    insertFirst = false
  ) {
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
    if (replaceIndex !== null && tablaBody.children[replaceIndex]) {
      if (Number(cliente.estadousuario)) {
        tablaBody.replaceChild(tr, tablaBody.children[replaceIndex]);
      } else {
        tablaBody.deleteRow(replaceIndex);
      }
    } else if (insertFirst) {
      if (Number(cliente.estadousuario)) {
        tablaBody.prepend(tr);
      }
    } else {
      tablaBody.appendChild(tr);
    }
  }

  function renderTable(url, withPreloader = true) {
    // Validar entradas
    if (!tablaBody || !txtLocal || !selLocal || !tipoClienteSelect ||
      !statusSelect || !origenSelect || !spanDesde || !spanHasta) {
      console.error("Elementos necesarios no están definidos");
      return;
    }
    const tipoFecha = document.querySelector(
      'input[name="tipoFecha"]:checked'
    );


    // ✅ Actualizar URL del navegador sin recargar
    window.history.pushState({}, "", url.toString());
    if (withPreloader) switchModal(preloader, true);

    const desde = spanDesde.textContent.trim() !== "Desde:" ? spanDesde.textContent.replace("Desde: ", "") : "";
    const hasta = spanHasta.textContent.trim() !== "Hasta:" ? spanHasta.textContent.replace("Hasta: ", "") : "";
    const filtros = {
      filtrarPorFecha: (desde != "" && hasta != ""),
      fechaAviso: tipoFecha ? (tipoFecha.value === "aviso") : false,
      desde: desde,
      hasta: hasta,
      tipoCliente: tipoClienteSelect.value,
      statusAtencion: statusSelect.value,
      origen: origenSelect.value,
      texto: txtLocal.value.toLowerCase().trim(),
      campoTexto: selLocal.value,
    };
    fetch(
      `${base}controller/cliente/obtener_lista.php?${url.searchParams.toString()}`,
      {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(filtros),
      }
    )
      .then((response) => response.text())
      .then((text) => {
        if (
          text.trim().startsWith("<!DOCTYPE") ||
          text.includes("<html") ||
          text.includes("<body")
        ) {
          console.log("Respuesta cruda del servidor:", text); // ⬅ Aquí ves el HTML o el error
          return;
        }
        try {
          // console.log(text);
          const json = JSON.parse(text);
          if (!json || !Array.isArray(json.clientes)) return;

          tablaBody.innerHTML = "";
          json.clientes.forEach((cliente) => renderRow(cliente, tablaBody));
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
          console.error("Error al parsear JSON:", e);
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
      .finally(() => {
        if (withPreloader) switchModal(preloader, false);
        // if (!withPreloader) console.log("OKOK");
      });
  }

  paginacion.renderFunction = renderTable;
  const limitParam =
    new URL(window.location.href).searchParams.get("limit") ?? 10;
  paginacion.rowsPerPage = limitParam;
  const pageParam = new URL(window.location.href).searchParams.get("page") ?? 1;
  paginacion.setCurrentPage(pageParam);
  const initialURL = paginacion.getURLConFiltros({ page: pageParam });
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

  // Sincronización con websocket
  // if(typeof conectarWebSocket  === "function" ){
  //   function obtenerIndicesPorValor(valoresBuscados) {
  //     const indices = {};

  //     Array.from(tablaBody.rows).forEach((row, index) => {
  //       const valorCelda = row.cells[1]?.textContent.trim(); // Asume que el valor está en la primera celda (columna 0)
  //       if (valoresBuscados.includes(Number(valorCelda))) {
  //         indices[index] = valorCelda;
  //       }
  //     });

  //     return indices;
  //   }
  //  window.globalSocket = conectarWebSocket((data) => {
  //     // Ejemplo: consultar datos de esos clientes al backend PHP
  //     const {nuevos, editados} = data;
  //     const todos = [...nuevos, ...editados];
  //     if (todos.length > 0) {
  //       if(nuevos && nuevos.length>0  && paginacion.currentPage === 1){
  //         // OBTENER INFO
  //         fetch(`${base}controller/cliente/obtener.php`, {
  //           method: "POST",
  //           headers: {
  //             "Content-Type": "application/json"
  //           },
  //           body: JSON.stringify({ ids: nuevos })
  //         })
  //           .then(res => res.json())
  //           .then(data => {
  //             const nuevos_clientes = data.clientes || [];
  //             // ORDENAR (opcional)

  //             // AGREGAR A FILAS
  //             nuevos_clientes.forEach((cliente) => renderRow(cliente, tablaBody, null, true));
  //             // ELIMINAR CANTIDAD DE NUMEROS AGREGADOS DEL FINAL
  //             const cantidadNuevos = nuevos_clientes.length;
  //             for (let i = 0; i < cantidadNuevos; i++) {
  //               const totalFilas = tablaBody.rows.length;
  //               if (totalFilas > 0) {
  //                 tablaBody.deleteRow(totalFilas - 1); // Eliminar última fila
  //               }
  //             }
  //             window.setFunctions?.();
  //           })
  //           .catch(error => {
  //             console.error("Error actualizando filas agregadas:", error);
  //           });
  //       }
  //       if(editados && editados.length > 0){
  //         // ELIMINAR IDS QUE NO SE ENCUENTREN EN LA TABLA
  //         const editados_filtrados = obtenerIndicesPorValor(editados);
  //         const ids = Object.values(editados_filtrados);
  //         // OBTENER INFO
  //         fetch(`${base}controller/cliente/obtener.php`, {
  //           method: "POST",
  //           headers: {
  //             "Content-Type": "application/json"
  //           },
  //           body: JSON.stringify({ ids: ids })
  //         })
  //           .then(res => res.json())
  //           .then(data => {
  //             const clientes_editados = data.clientes || [];
  //             // ACTUALIZAR FILAS
  //             clientes_editados.forEach(cliente => {
  //               // Buscar el índice en la tabla basado en el ID
  //               const index = parseInt(
  //                 Object.keys(editados_filtrados).find(i => editados_filtrados[i] === String(cliente.idcliente))
  //               );
  //               if (!isNaN(index)) {
  //                 renderRow(cliente, tablaBody, index); // reemplaza fila en ese índice
  //               }
  //             });
  //             window.setFunctions?.();
  //           })
  //           .catch(error => {
  //             console.error("Error actualizando filas editadas:", error);
  //           });
  //       }
  //     }
  // });
  // }

  // Sincronización mediante conexión

  if (typeof conectarSincronizacion === "function") {
    if (conectarSincronizacion()) {
      setInterval(() => {
        const pageParam =
          new URL(window.location.href).searchParams.get("page") ?? 1;
        const initialURL = paginacion.getURLConFiltros({ page: pageParam });
        renderTable(initialURL, false);
      }, 10000);
    }
  }
});
