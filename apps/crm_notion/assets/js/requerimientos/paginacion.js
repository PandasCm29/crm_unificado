export const paginacion = {
  currentPage: 1,
  rowsPerPage: 10,
  totalRecords: 0,
  totalPages: 0,
  getQueryParams() {
    return {
      page: this.currentPage,
      limit: this.rowsPerPage,
    };
  },
  updateRowsPerPage(newRowsPerPage) {
    this.rowsPerPage = newRowsPerPage;
    this.currentPage = 1;
    if (typeof this.renderFunction === "function") {
      const url = this.getURLConFiltros();
      this.renderFunction(url);
    }
  },
  setCurrentPage(pageNumber) {
    this.currentPage = Number(pageNumber);
  },

  updatePaginationInfo(totalRecords) {
    this.totalRecords = totalRecords;
    this.totalPages = Math.ceil(totalRecords / this.rowsPerPage);
  },
  goToPage(page) {
    if (page >= 1 && page <= this.totalPages) {
      this.currentPage = page;
      if (typeof this.renderFunction === "function") {
        const url = this.getURLConFiltros();
        this.renderFunction(url);
      }
    }
  },
  nextPage() {
    if (this.currentPage < this.totalPages) {
      this.currentPage++;
      if (typeof this.renderFunction === "function") {
        const url = this.getURLConFiltros();
        this.renderFunction(url);
      }
    }
  },
  prevPage() {
    if (this.currentPage > 1) {
      this.currentPage--;
      if (typeof this.renderFunction === "function") {
        const url = this.getURLConFiltros();
        this.renderFunction(url);
      }
    }
  },
  renderPaginationControls() {
    const container = document.getElementById("pagination-container");
    if (!container) return;

    if (this.totalPages <= 1) {
      container.innerHTML = "";
      return;
    }

    const createPageBtn = (
      label,
      page,
      isActive = false,
      isDisabled = false
    ) => `
            <button onclick="paginacion.goToPage(${page})"
                ${isDisabled ? "disabled" : ""}
                class="w-8 h-8 text-sm rounded-md transition-colors
                    ${
                      isActive
                        ? "bg-orange-500 text-white font-semibold"
                        : "text-gray-800 hover:bg-gray-200"
                    } 
                    disabled:opacity-50 disabled:cursor-not-allowed">
                ${label}
            </button>
        `;
    this.currentPage = Number(this.currentPage);
    const start = (this.currentPage - 1) * this.rowsPerPage + 1;
    const end = Math.min(
      this.currentPage * this.rowsPerPage,
      this.totalRecords
    );

    let html = `
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 py-3">
                <div class="text-sm text-gray-700">
                    Mostrando ${start} a ${end} de ${this.totalRecords} resultados
                </div>
                <nav class="flex items-center justify-center gap-1 ">
        `;
    //BONTON ANTERIOR (<)
    html += createPageBtn(
      "‹",
      this.currentPage - 1,
      false,
      this.currentPage === 1
    );
    const pages = [];
    if (this.totalPages <= 7) {
      for (let i = 1; i <= this.totalPages; i++) pages.push(i);
    } else {
      if (this.currentPage <= 4) {
        pages.push(1, 2, 3, 4, 5, "...", this.totalPages);
      } else if (this.currentPage >= this.totalPages - 3) {
        pages.push(
          1,
          "...",
          this.totalPages - 4,
          this.totalPages - 3,
          this.totalPages - 2,
          this.totalPages - 1,
          this.totalPages
        );
      } else {
        pages.push(
          1,
          "...",
          this.currentPage - 1,
          this.currentPage,
          this.currentPage + 1,
          "...",
          this.totalPages
        );
      }
    }
    for (const p of pages) {
      if (p === "...") {
        html += `<span class="w-8 h-8 flex items-center justify-center text-gray-500">…</span>`;
      } else {
        html += createPageBtn(p, p, this.currentPage === Number(p));
      }
    }
    //BOTON SIGUIENTE (>)
    html += createPageBtn(
      "›",
      this.currentPage + 1,
      false,
      this.currentPage === this.totalPages
    );

    html += `</nav></div>`;
    container.innerHTML = html;
  },
  getURLConFiltros(extra = {}) {
    const url = new URL(window.location.href);
    const searchParams = new URLSearchParams(url.search);

    // Agregar o reemplazar parámetros // TODO: probar en Requerimientos
    if(Object.keys(extra).length === 0){
      extra.page = this.currentPage;
    }else{
      this.currentPage = extra.page;      
    }
    extra.limit = this.rowsPerPage;

    Object.entries(extra).forEach(([key, value]) => {
      searchParams.set(key, value);
    });

    const finalURL = new URL(window.location.origin + window.location.pathname);
    finalURL.search = searchParams.toString();
    return finalURL;
  },
};
export function updateRowsPerPage(id) {
  const newRowsPerPage = parseInt(document.getElementById(id)?.value || 10);
  paginacion.updateRowsPerPage(newRowsPerPage);
}
if (typeof window !== "undefined") {
  window.paginacion = paginacion;
  window.updateRowsPerPage = updateRowsPerPage;
}
