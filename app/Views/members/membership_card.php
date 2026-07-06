<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Membership Card</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= \App\Helpers\Url::appUrl() ?>/uploads/gafconl-icon.png">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        html, body { height: 100%; margin: 0; padding: 0; overflow: hidden; }
        body { background: #f8f9fa; min-height: 100vh; }
        .id-card-container {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            height: 100vh;
            width: 100vw;
            overflow: hidden;
        }
        .id-card {
            width: 340px;
            height: 520px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.10);
            padding: 18px 20px 12px 20px;
            position: relative;
            font-family: 'Segoe UI', Arial, sans-serif;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            overflow: hidden;
        }
        .id-card-header, .id-card-body, .id-card-footer {
            width: 100%;
        }
        .id-card-header {
            flex-direction: column;
            align-items: center;
            text-align: center;
            display: flex;
        }
        .id-card-logo {
            height: 38px;
        }
        .id-card-title {
            font-size: 1.3rem;
            font-weight: bold;
            letter-spacing: 1px;
            margin-top: 0.7rem;
            margin-bottom: 0.7rem;
        }
        .id-card-photo {
            width: 160px;
            height: 160px;
            object-fit: cover;
            border-radius: 16px;
            border: 1.5px solid #e0e0e0;
            background: #f8f9fa;
            margin: 0.7rem auto 0.7rem auto;
            display: block;
        }
        .id-card-details {
            text-align: center;
            margin: 0;
            font-size: 1.08rem;
            margin-bottom: 1.2rem;
        }
        .id-card-details strong {
            font-size: 1.15em;
        }
        .id-card-footer {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            margin-top: auto;
            padding-top: 18px;
        }
        .id-card-qr {
            width: 64px;
            height: 64px;
        }
        .id-card-signature {
            font-size: 0.95em;
            color: #888;
            width: 140px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .id-card-signature img {
            height: 28px;
            margin-bottom: 2px;
            margin-top: 12px;
        }
        .id-card-signature .sig-line {
            width: 100%;
            border-top: 1.5px solid #ccc;
            margin: 2px 0 2px 0;
        }
        .id-card-signature span {
            font-size: 0.98em;
            margin-top: 2px;
        }
        @media print {
            body, html { background: #fff !important; }
            .id-card-container { min-height: 0; }
            .id-card { box-shadow: none; margin: 0 auto; }
            .print-btn { display: none !important; }
        }
    </style>
</head>
<body>
<div class="id-card-container" style="height:100vh; justify-content:center; align-items:center; display:flex; flex-direction:column;">
    <div class="d-flex justify-content-center gap-2 print-btn" style="margin-bottom:16px;">
        <button class="btn btn-primary btn-sm" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
        <button class="btn btn-success btn-sm" onclick="downloadCard()"><i class="fas fa-download"></i> Download</button>
    </div>
    <div class="id-card">
        <div class="id-card-header">
            <img src="<?= \App\Helpers\Url::base(true) ?>/uploads/gafconl_colored-447x87.png" alt="Logo" class="id-card-logo mb-1">
            <div class="id-card-title">MEMBER CARD</div>
            <?php 
                $photoRaw = $member['photo'] ?? '';
                $photo = (!empty($photoRaw) && strtolower($photoRaw) !== 'default.jpg') ? htmlspecialchars($photoRaw) : 'default-user.png';
                $photoPath = \App\Helpers\Url::base(true) . '/uploads/member_photos/' . $photo;
            ?>
            <img src="<?= $photoPath ?>" alt="Member Photo" class="id-card-photo">
        </div>
        <div class="id-card-details text-center">
            <strong>#<?= htmlspecialchars($member['membership_number'] ?? '') ?></strong><br>
            <?= htmlspecialchars(strtoupper($member['firstname'] ?? '') . ' ' . strtoupper($member['surname'] ?? '')) ?><br>
            <span style="font-size:0.95em;">CHAPTER: <?= htmlspecialchars(strtoupper($member['chapter'] ?? '')) ?></span><br>
        </div>
        <div class="id-card-footer">
            <?php 
                $qrData = "Member: " . ($member['membership_number'] ?? '') . "\nName: " . ($member['firstname'] ?? '') . ' ' . ($member['surname'] ?? '') . "\nEmail: " . ($member['email'] ?? '');
                $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=64x64&data=' . urlencode($qrData);
            ?>
            <div style="display: flex; flex-direction: column; align-items: center; gap: 8px;">
                <img src="<?= $qrUrl ?>" alt="QR Code" class="id-card-qr mb-1">
            </div>
            <div class="id-card-signature">
                <img src="<?= \App\Helpers\Url::base(true) ?>/uploads/signature.png" alt="Signature">
                <div class="sig-line"></div>
                <span>AUTHORIZED SIGNATURE</span>
            </div>
        </div>
    </div>
</div>
<script>
function downloadCard() {
    // Hide the print buttons temporarily
    const printButtons = document.querySelector('.print-btn');
    printButtons.style.display = 'none';
    
    // Convert the card to canvas
    html2canvas(document.querySelector('.id-card'), {
        backgroundColor: '#ffffff',
        scale: 2, // Higher quality
        useCORS: true,
        allowTaint: true
    }).then(function(canvas) {
        // Show the print buttons again
        printButtons.style.display = 'flex';
        
        // Create download link
        const link = document.createElement('a');
        link.download = 'membership-card-<?= htmlspecialchars($member['membership_number'] ?? 'member') ?>.png';
        link.href = canvas.toDataURL('image/png');
        link.click();
    }).catch(function(error) {
        console.error('Error generating download:', error);
        printButtons.style.display = 'flex';
        alert('Error generating download. Please try again.');
    });
}
</script>
</body>
</html> 