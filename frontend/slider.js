document.addEventListener('DOMContentLoaded', () => {
    // 1. Ambil semua elemen yang diperlukan
    const sliderWrapper = document.querySelector('.slider-wrapper');
    const slides = document.querySelectorAll('.phone-image');
    const dots = document.querySelectorAll('.dot');
    const totalSlides = slides.length;

    // Pengaturan Otomatisasi
    let currentSlide = 0; // Index slide saat ini (Mulai dari 0)
    const intervalTime = 4000; // 4000ms = 4 detik (Anda bisa mengubah nilai ini)
    let slideInterval; // Variabel untuk menampung ID interval

    // Safety check: Hentikan jika elemen tidak ditemukan
    if (!sliderWrapper || totalSlides === 0) {
        console.error("Elemen slider atau gambar tidak ditemukan.");
        return;
    }

    // --- FUNGSI INTI ---

    // Fungsi utama untuk menggeser slider dan memperbarui dot
    function updateSlider() {
        // Hitung persentase pergeseran per slide (misalnya, 100% / 3 = 33.333%)
        const slideWidthPercentage = 100 / totalSlides;

        // Geser slider-wrapper ke kiri (offset negatif)
        const offset = -currentSlide * slideWidthPercentage;

        // Terapkan pergeseran (menggunakan transform agar lebih smooth dan cepat)
        sliderWrapper.style.transform = `translateX(${offset.toFixed(3)}%)`;

        // Perbarui tampilan dots
        dots.forEach((dot) => {
            dot.classList.remove('active');
        });

        // Tambahkan class 'active' ke dot yang sesuai
        if (dots[currentSlide]) {
            dots[currentSlide].classList.add('active');
        }
    }

    // Fungsi untuk menggeser slide secara otomatis
    function autoSlide() {
        currentSlide++;
        // Jika sudah mencapai akhir, kembali ke slide pertama (0)
        if (currentSlide >= totalSlides) {
            currentSlide = 0;
        }
        updateSlider();
    }

    // --- INTERAKSI PENGGUNA (DOTS) ---

    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            // 1. Hentikan auto-slide agar pengguna bisa melihat slide yang dipilih
            clearInterval(slideInterval);

            // 2. Atur slide saat ini ke indeks dot yang diklik
            currentSlide = index;
            updateSlider();

            // 3. Mulai kembali auto-slide setelah klik
            slideInterval = setInterval(autoSlide, intervalTime);
        });
    });

    // --- INISIALISASI DAN START ---

    // 1. Tampilkan slide pertama saat halaman dimuat
    updateSlider();

    // 2. Mulai otomatisasi
    slideInterval = setInterval(autoSlide, intervalTime);
});