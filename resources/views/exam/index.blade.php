<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - CAT CPNS 2024</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #e67e22;
            --secondary-color: #2c3e50;
            --accent-color: #d35400;
        }

        body { 
            background: radial-gradient(circle at top right, #2c3e50, #000);
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            overflow-x: hidden;
        }

        /* Elemen Dekoratif Background */
        .bg-circle {
            position: absolute;
            width: 300px;
            height: 300px;
            background: rgba(230, 126, 34, 0.1);
            filter: blur(80px);
            border-radius: 50%;
            z-index: -1;
        }

        .login-card { 
            border-radius: 24px; 
            border: 1px solid rgba(255, 255, 255, 0.1); 
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            transition: transform 0.3s ease;
        }

        .login-card:hover {
            transform: translateY(-5px);
        }

        .brand-logo {
            width: 80px;
            height: 80px;
            background: var(--primary-color);
            color: white;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin: 0 auto 20px;
            box-shadow: 0 10px 20px rgba(230, 126, 34, 0.3);
        }

        .form-label {
            color: var(--secondary-color);
            font-size: 0.9rem;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-control, .form-select {
            border-radius: 12px;
            padding: 12px 16px;
            border: 2px solid #eee;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(230, 126, 34, 0.1);
        }

        .btn-start { 
            background: linear-gradient(to right, var(--primary-color), var(--accent-color));
            border: none; 
            border-radius: 12px;
            padding: 14px;
            font-weight: 700; 
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            text-transform: uppercase;
        }

        .btn-start:hover { 
            background: linear-gradient(to right, var(--accent-color), #bf4b00);
            box-shadow: 0 10px 20px rgba(211, 84, 0, 0.4);
            transform: scale(1.02);
        }

        .instruction-box {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 15px;
            margin-bottom: 25px;
            border-left: 4px solid var(--primary-color);
        }

        .instruction-box ul {
            padding-left: 20px;
            margin-bottom: 0;
            font-size: 0.85rem;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="bg-circle" style="top: -50px; left: -50px;"></div>
    <div class="bg-circle" style="bottom: -50px; right: -50px;"></div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-sm-10 px-4">
                <div class="card login-card p-4 p-lg-5">
                    <div class="text-center mb-4">
                        <div class="brand-logo">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <h2 class="fw-bold text-dark mb-1">SIMULASI CAT</h2>
                        <p class="text-muted small">Portal Latihan CPNS 2024</p>
                    </div>

                    <div class="instruction-box">
                        <p class="fw-bold mb-1 small"><i class="fas fa-info-circle me-1"></i> Aturan Main:</p>
                        <ul>
                            <li>Waktu pengerjaan: 100 Menit</li>
                            <li>Total soal: 110 Butir (TWK, TIU, TKP)</li>
                            <li>Pastikan koneksi internet stabil</li>
                        </ul>
                    </div>

                    <form action="{{ route('exam.start') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-signature text-primary"></i> Nama Lengkap Peserta
                            </label>
                            <input type="text" name="name" class="form-control" placeholder="Contoh: Budi Santoso" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="fas fa-list-check text-primary"></i> Pilih Paket Soal
                            </label>
                            <select class="form-select" name="type">
    <option value="paket_1">Simulasi Mandiri - Paket 1</option>
    <option value="paket_2">Simulasi Mandiri - Paket 2</option>
    <option value="paket_3">Try Out Nasional - Paket 3</option>
    <option value="paket_4">Prediksi SKD - Paket 4</option>
    <option value="paket_5">Latihan Maraton - Paket 5</option>
</select>
                        </div>

                        <button type="submit" class="btn btn-start w-100 text-white shadow-sm">
                            Mulai Simulasi <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </form>
                    
                    <div class="text-center mt-4">
                        <p class="text-muted extra-small" style="font-size: 0.75rem;">
                            &copy; 2024 Portal CAT. Hak Cipta Dilindungi.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>