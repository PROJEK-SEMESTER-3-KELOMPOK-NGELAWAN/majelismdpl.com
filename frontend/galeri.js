const fileInput = document.getElementById('fileInput');
    const imagePreview = document.getElementById('imagePreview');
    const galleryGrid = document.getElementById('galleryGrid');

    fileInput.addEventListener('change', () => {
      const file = fileInput.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = e => {
          imagePreview.src = e.target.result;
          imagePreview.style.display = 'block';
        };
        reader.readAsDataURL(file);
      } else {
        imagePreview.style.display = 'none';
        imagePreview.src = '';
      }
    });

    document.getElementById('formUpload').addEventListener('submit', e => {
      e.preventDefault();
      const file = fileInput.files[0];
      if (!file) {
        alert('Pilih gambar terlebih dahulu.');
        return;
      }

      const reader = new FileReader();
      reader.onload = e => {
        addImageToGallery(e.target.result, file.name);
        fileInput.value = '';
        imagePreview.style.display = 'none';
        imagePreview.src = '';
        alert('Gambar berhasil diupload.');
      };
      reader.readAsDataURL(file);
    });

    function addImageToGallery(src, caption) {
      const div = document.createElement('div');
      div.className = 'gallery-item';
      div.innerHTML = `<img src="${src}" alt="${caption}"><div class="caption mt-2 text-center fw-semibold text-brown">${caption}</div>`;
      galleryGrid.appendChild(div);
    }
    

    