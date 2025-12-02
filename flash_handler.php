<style>
    /* Import Font jika belum ada di parent (Opsional) */
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Plus+Jakarta+Sans:wght@400;500;700&display=swap');
    
    :root {
        --primary: #9C7E5C;
        --primary-dark: #7B5E3A;
        --text-body-gray: #546E7A;
    }

    /* Container Popup */
    .custom-theme-popup {
        border-radius: 30px !important;
        padding: 2.5rem 2rem !important;
        box-shadow: 0 20px 60px rgba(0,0,0,0.15) !important;
        font-family: 'Plus Jakarta Sans', sans-serif !important;
    }

    /* Icon Container */
    .swal-custom-icon-container {
        width: 80px;
        height: 80px;
        background: var(--primary);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem auto;
        box-shadow: 0 10px 20px -5px rgba(156, 126, 92, 0.4);
    }
    
    .swal-custom-icon-container.error {
        background: #D32F2F;
    }

    .swal-custom-icon-container i {
        color: white;
        font-size: 2.5rem;
    }

    /* Text Styling */
    .swal-custom-title {
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        color: var(--primary);
        font-size: 1.8rem;
        margin-bottom: 0.8rem;
    }
    
    .swal-custom-title.error {
        color: #D32F2F;
    }

    .swal-custom-text {
        font-family: 'Plus Jakarta Sans', sans-serif;
        color: var(--text-body-gray);
        font-size: 1.1rem;
        margin-bottom: 1rem;
    }

    /* Button Styling - FIX BORDER & OUTLINE */
    .btn-swal-custom {
        padding: 0.8rem 2.5rem;
        border-radius: 12px;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-weight: 700;
        font-size: 1rem;
        border: none !important; /* Hapus border hitam */
        outline: none !important; /* Hapus outline fokus */
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        background-color: var(--primary);
        color: white;
    }

    .btn-swal-custom:hover {
        background-color: var(--primary-dark);
        transform: translateY(-3px);
        box-shadow: 0 8px 15px rgba(156, 126, 92, 0.3);
    }
    
    /* Mencegah border muncul saat tombol diklik/fokus */
    .btn-swal-custom:focus, 
    .btn-swal-custom:active {
         border: none !important;
         outline: none !important;
         box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
</style>

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />

<?php if (isset($_SESSION['flash_swal'])): ?>
    <?php 
        $flash = $_SESSION['flash_swal'];
        
        // Tentukan ikon dan kelas styling berdasarkan tipe
        $isSuccess = ($flash['type'] === 'success');
        
        $iconHtml = $isSuccess
            ? '<div class="swal-custom-icon-container"><i class="bi bi-check-lg"></i></div>' 
            : '<div class="swal-custom-icon-container error"><i class="bi bi-x-lg"></i></div>';
            
        $titleClass = $isSuccess ? '' : 'error';
        
        // Gunakan json_encode agar aman saat dimasukkan ke Javascript
        $jsTitle = json_encode($flash['title']);
        $jsText = json_encode($flash['text']);
        $jsBtn = json_encode($flash['buttonText']);
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                html: `
                    <div class='swal-custom-content'>
                        <?= $iconHtml ?>
                        <h2 class='swal-custom-title <?= $titleClass ?>'>${<?= $jsTitle ?>}</h2>
                        <p class='swal-custom-text'>${<?= $jsText ?>}</p>
                    </div>
                `,
                confirmButtonText: <?= $jsBtn ?>,
                buttonsStyling: false,
                customClass: {
                    popup: 'custom-theme-popup',
                    confirmButton: 'btn-swal-custom'
                },
                // Backdrop transparan gelap agar background halaman index tetap terlihat
                backdrop: `rgba(0,0,0,0.5)`,
                allowOutsideClick: false,
                allowEscapeKey: false
            });
        });
    </script>
    <?php 
    // Hapus pesan dari session setelah ditampilkan
    unset($_SESSION['flash_swal']); 
    ?>
<?php endif; ?>