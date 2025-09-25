<?php
require_once 'auth_check.php';

// Ambil data trip berdasarkan id jika perlu dari database (contoh sederhana)
$id = $_GET['id'] ?? null;
// Jika pakai koneksi DB, bisa query trip berdasarkan $id

?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Detail Trip | Majelis MDPL</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />
  <style>
    body {
      background: #f6f0e8;
      font-family: 'Poppins', Arial, sans-serif;
      color: #232323;
      min-height: 100vh;
      padding: 30px;
    }

    .container-detail {
      max-width: 700px;
      margin: 0 auto;
      background: white;
      padding: 30px 40px;
      border-radius: 18px;
      box-shadow: 0 6px 30px rgba(79, 56, 34, 0.1);
    }

    h1 {
      color: #a97c50;
      font-weight: 700;
      margin-bottom: 25px;
      text-align: center;
      letter-spacing: 1.2px;
    }

    label {
      font-weight: 600;
      color: #624922;
      margin-top: 15px;
    }

    textarea.form-control {
      resize: vertical;
      min-height: 80px;
      font-size: 0.95em;
    }

    input.form-control,
    textarea.form-control {
      border: 1.8px solid #d9b680;
      border-radius: 10px;
      padding: 10px 14px;
      transition: border-color 0.3s ease;
    }

    input.form-control:focus,
    textarea.form-control:focus {
      border-color: #a97c50;
      box-shadow: 0 0 8px #a97c50b3;
      outline: none;
    }

    #mapPreview {
      margin-top: 12px;
      width: 100%;
      height: 280px;
      border-radius: 12px;
      border: 2px solid #d9b680;
      box-shadow: 0 4px 16px rgba(169, 124, 80, 0.25);
      display: none;
    }

    .btn-submit {
      background-color: #a97c50;
      border: none;
      color: white;
      padding: 12px 30px;
      font-weight: 700;
      font-size: 1.1em;
      border-radius: 12px;
      cursor: pointer;
      margin-top: 25px;
      display: block;
      width: 100%;
      transition: background-color 0.3s ease;
    }

    .btn-submit:hover {
      background-color: #7a5f34;
    }
  </style>
</head>

<body>

  <div class="container-detail">
    <h1>Detail Trip</h1>
    <form action="save_detailtrip.php" method="POST">
      <input type="hidden" name="id_trip" value="<?= htmlspecialchars($id) ?>" />

      <label for="nama_lokasi">Nama Lokasi</label>
      <input type="text" id="nama_lokasi" name="nama_lokasi" class="form-control" required />

      <label for="alamat">Alamat</label>
      <textarea id="alamat" name="alamat" class="form-control" required></textarea>

      <label for="waktu_kumpul">Waktu Kumpul</label>
      <input type="text" id="waktu_kumpul" name="waktu_kumpul" class="form-control" placeholder="Contoh: 07.00 WIB, di Basecamp..." required />

      <label for="include">Include</label>
      <textarea id="include" name="include" class="form-control" placeholder="Apa saja yang sudah termasuk dalam trip" required></textarea>

      <label for="exclude">Exclude</label>
      <textarea id="exclude" name="exclude" class="form-control" placeholder="Apa saja yang tidak termasuk dalam trip" required></textarea>

      <label for="syarat_ketentuan">Syarat & Ketentuan</label>
      <textarea id="syarat_ketentuan" name="syarat_ketentuan" class="form-control" placeholder="Tuliskan syarat dan ketentuan" required></textarea>

      <label for="link_gmap">Link Google Map</label>
      <input type="url" id="link_gmap" name="link_gmap" class="form-control" placeholder="https://maps.google.com/..." />

      <!-- Google Map Preview -->
      <iframe id="mapPreview" src="" allowfullscreen loading="lazy"></iframe>

      <button type="submit" class="btn-submit">Simpan Detail</button>
    </form>
  </div>

  <script>
    const inputLink = document.getElementById('link_gmap');
    const mapPreview = document.getElementById('mapPreview');

    inputLink.addEventListener('input', function () {
      const url = this.value.trim();

      if (url) {
        let embedUrl = '';

        // Jika sudah embed URL Google Maps atau alamat biasa, ubah menjadi embed format
        try {
          const urlObj = new URL(url);
          if (urlObj.hostname.includes('google.com') || urlObj.hostname.includes('goo.gl')) {
            if (url.includes('/maps/embed')) {
              embedUrl = url; // langsung embed jika sudah embed url
            } else {
              // Ubah URL maps biasa jadi embed
              // Contoh: https://www.google.com/maps/place/LOC -> https://www.google.com/maps/embed/v1/place?q=LOC&key=YOUR_KEY
              // Namun tanpa API key, kita manfaatkan bagian /maps/place/ atau /maps/ untuk embed dengan cara sederhana

              // Simplify for direct use: replace /maps/ with /maps/embed/
              embedUrl = url.replace('/maps/', '/maps/embed/');
            }
            // Tampilkan iframe preview dengan src URL embed
            mapPreview.src = embedUrl;
            mapPreview.style.display = "block";
          } else {
            // Link bukan google maps, sembunyikan preview
            mapPreview.style.display = "none";
            mapPreview.src = '';
          }
        } catch (error) {
          // URL tidak valid, sembunyikan preview
          mapPreview.style.display = "none";
          mapPreview.src = '';
        }
      } else {
        // Jika kosong, sembunyikan preview
        mapPreview.style.display = "none";
        mapPreview.src = '';
      }
    });
  </script>

</body>

</html>