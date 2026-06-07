<?php
require_once 'auth.php';
check_auth('student');

$page_title = "Öğrenci Paneli";
include 'includes/header.php';

$oid = $_SESSION['user_id'];

// Karne / Öğrenci Bilgileri
$ogrenci = $db->query("SELECT * FROM ogrenciler WHERE id = $oid")->fetch();

// Notlar
$grades = $db->query("
    SELECT n.*, d.ders_adi
    FROM notlar n
    JOIN dersler d ON n.ders_id = d.id
    WHERE n.ogrenci_id = $oid
    ORDER BY d.ders_adi
")->fetchAll();

// Devamsızlık
$attendance = $db->query("SELECT * FROM devamsizlik WHERE ogrenci_id = $oid ORDER BY tarih DESC")->fetchAll();
$toplam_dev = 0;
foreach ($attendance as $a) {
    $toplam_dev += ($a['durum'] == 'Tam') ? 1 : 0.5;
}

// Ortalamalar
$ortalamalar = array_filter(array_column($grades, 'ortalama'), fn($v) => $v !== null);
$genel = count($ortalamalar) > 0 ? array_sum($ortalamalar) / count($ortalamalar) : 0;
$gecti = $genel >= 50;
?>

<div style="margin-bottom: 1rem; text-align: right;">
    <button onclick="window.print()" class="btn btn-primary" style="background: #f97316;">Karnemi Yazdır</button>
</div>

<!-- ÖĞRENCİ BİLGİLERİ -->
<div id="karne" style="background:#fff; padding:15px; border-radius:5px; margin-bottom:20px; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
    <h3 style="border-bottom:1px solid #ddd; padding-bottom:10px; margin-bottom:15px;">Öğrenci Bilgileri</h3>
    <table style="width:100%; font-size:0.875rem;">
        <tr>
            <td><strong>Ad Soyad:</strong> <?= htmlspecialchars($ogrenci['ad'].' '.$ogrenci['soyad']) ?></td>
            <td style="text-align:right;"><strong>Sınıf:</strong> <?= htmlspecialchars($ogrenci['sinif']) ?></td>
        </tr>
        <tr>
            <td><strong>Öğrenci No:</strong> <?= htmlspecialchars($ogrenci['ogr_no']) ?></td>
            <td style="text-align:right;"><strong>Toplam Devamsızlık:</strong> <?= $toplam_dev ?> gün</td>
        </tr>
    </table>
    
    <div style="margin-top:1rem; display:flex; justify-content:space-between; padding-top:0.75rem; border-top:1px solid #e2e8f0; font-size:0.875rem;">
        <div>
            <strong>Genel Ortalama:</strong>
            <span style="font-weight:700; color:<?= $gecti ? '#16a34a' : '#dc2626' ?>; margin-left:0.5rem;">
                <?= number_format($genel, 2) ?>
            </span>
            <span style="font-size:0.75rem; color:#94a3b8; margin-left:0.25rem;">(VizeOrt×%40 + Final×%60)</span>
        </div>
        <div>
            <span class="badge <?= $gecti ? 'badge-success' : 'badge-danger' ?>" style="padding:0.4rem 1rem;">
                <?= $gecti ? 'GEÇTİ' : 'KALDI' ?>
            </span>
        </div>
    </div>
</div>

<!-- NOTLAR -->
<div id="notlar" style="background:#fff; padding:15px; border-radius:5px; margin-bottom:20px; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
    <h3 style="border-bottom:1px solid #ddd; padding-bottom:10px; margin-bottom:15px;">Notlarım</h3>
    <table class="table" style="border: 1px solid #e2e8f0;">
        <thead>
            <tr style="background: #f8fafc;">
                <th>Dersin Adı</th>
                <th style="text-align:center;">Vize 1</th>
                <th style="text-align:center;">Vize 2</th>
                <th style="text-align:center;">Vize 3</th>
                <th style="text-align:center;">Final</th>
                <th style="text-align:center;">Ortalama</th>
                <th style="text-align:center;">Durum</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($grades as $g): ?>
            <tr>
                <td><?= htmlspecialchars($g['ders_adi']) ?></td>
                <td style="text-align:center;"><?= $g['vize1'] ?? '-' ?></td>
                <td style="text-align:center;"><?= $g['vize2'] ?? '-' ?></td>
                <td style="text-align:center;"><?= $g['vize3'] ?? '-' ?></td>
                <td style="text-align:center;"><?= $g['final']  ?? '-' ?></td>
                <td style="text-align:center;"><strong><?= $g['ortalama'] !== null ? number_format($g['ortalama'],2) : '-' ?></strong></td>
                <td style="text-align:center;">
                    <?php if($g['ortalama'] !== null): ?>
                        <span class="badge <?= $g['ortalama'] >= 50 ? 'badge-success' : 'badge-danger' ?>">
                            <?= $g['ortalama'] >= 50 ? 'Geçti' : 'Kaldı' ?>
                        </span>
                    <?php else: ?>-<?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if(!$grades): ?>
            <tr><td colspan="7" style="text-align:center; color:#94a3b8; padding:1.5rem;">Henüz not girilmemiş.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- DEVAMSIZLIK -->
<div id="devamsizlik" style="background:#fff; padding:15px; border-radius:5px; margin-bottom:20px; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
    <h3 style="border-bottom:1px solid #ddd; padding-bottom:10px; margin-bottom:15px;">Devamsızlık Kayıtlarım</h3>
    <table class="table">
        <thead>
            <tr style="background: #f8fafc;">
                <th>Tarih</th>
                <th>Durum</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($attendance as $a): ?>
            <tr>
                <td><?= date('d.m.Y', strtotime($a['tarih'])) ?></td>
                <td><?= $a['durum'] == 'Tam' ? 'Tam Gün' : 'Yarım Gün' ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$attendance): ?>
            <tr><td colspan="2" style="color:#94a3b8;">Devamsızlık kaydı yok.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
@media print {
    .sidebar, .page-header, button { display: none !important; }
    .main-content { margin-left: 0 !important; }
    #devamsizlik { display: none !important; }
    body { background: white !important; }
    #karne, #notlar { box-shadow: none !important; margin-bottom: 30px; }
}
</style>

<?php include 'includes/footer.php'; ?>
