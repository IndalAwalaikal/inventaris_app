// Inisialisasi DataTables untuk tabel produk dan kategori
$(document).ready(function () {
  $("#productsTable, #categoriesTable").DataTable({
    language: {
      url: "//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json",
    },
    pageLength: 10,
    order: [[0, "desc"]],
  });
});

// Pratinjau gambar sebelum upload
function previewImage(event) {
  const imagePreview = document.getElementById("imagePreview");
  imagePreview.src = URL.createObjectURL(event.target.files[0]);
  imagePreview.style.display = "block";
}

// Konfirmasi hapus menggunakan SweetAlert2
function confirmDelete(url) {
  Swal.fire({
    title: "Konfirmasi Hapus",
    text: "Apakah Anda yakin ingin menghapus data ini?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#3085d6",
    confirmButtonText: "Hapus",
    cancelButtonText: "Batal",
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = url;
    }
  });
}
