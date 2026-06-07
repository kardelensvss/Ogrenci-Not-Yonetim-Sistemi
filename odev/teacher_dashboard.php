<?php
require_once 'auth.php';
check_auth('teacher');

// TOPLU İŞLEMLER
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['islem']) && $_POST['islem'] == 'ogrenci_ekle') {
        try {
            $db->prepare("INSERT INTO ogrenciler (ogr_no, ad, soyad, sinif, sifre) VALUES (?,?,?,?,?)")
               ->execute([trim($_POST['ogr_no']), trim($_POST['ad']), trim($_POST['soyad']), trim($_POST['sinif']), trim($_POST['sifre'])]);
            $success = "Öğrenci eklendi.";
        } catch (PDOException $e) { $error = "Hata: Öğrenci numarası zaten var."; }
    }
    
    if (isset($_POST['islem']) && $_POST['islem'] == 'ders_ekle') {
        try {
            $db->prepare("INSERT INTO dersler (ders_adi) VALUES (?)")->execute([trim($_POST['ders_adi'])]);
            $success = "Ders eklendi.";
        } catch (PDOException $e) { $error = "Hata oluştu."; }
    }
    
    if (isset($_POST['islem']) && $_POST['islem'] == 'not_gir') {
        $v1 = ($_POST['vize1'] !== '') ? (float)$_POST['vize1'] : null;
        $v2 = ($_POST['vize2'] !== '') ? (float)$_POST['vize2'] : null;
        $v3 = ($_POST['vize3'] !== '') ? (float)$_POST['vize3'] : null;
        $fin = ($_POST['final'] !== '') ? (float)$_POST['final'] : null;
        
        $vizeler = array_filter([$v1, $v2, $v3], fn($v) => $v !== null);
        $v_ort = count($vizeler) > 0 ? array_sum($vizeler) / count($vizeler) : null;
        
        $ort = null;
        if ($v_ort !== null && $fin !== null) { $ort = ($v_ort * 0.4) + ($fin * 0.6); }
        elseif ($v_ort !== null) { $ort = $v_ort * 0.4; }
        elseif ($fin !== null) { $ort = $fin * 0.6; }
        
        $c = $db->prepare("SELECT id FROM notlar WHERE ogrenci_id=? AND ders_id=?");
        $c->execute([(int)$_POST['ogrenci_id'], (int)$_POST['ders_id']]);
        if ($ex = $c->fetchColumn()) {
            $db->prepare("UPDATE notlar SET vize1=?, vize2=?, vize3=?, final=?, ortalama=? WHERE id=?")
               ->execute([$v1, $v2, $v3, $fin, $ort, $ex]);
        } else {
            $db->prepare("INSERT INTO notlar (ogrenci_id, ders_id, vize1, vize2, vize3, final, ortalama) VALUES (?,?,?,?,?,?,?)")
               ->execute([(int)$_POST['ogrenci_id'], (int)$_POST['ders_id'], $v1, $v2, $v3, $fin, $ort]);
        }
        $success = "Not kaydedildi.";
    }
    
    if (isset($_POST['islem']) && $_POST['islem'] == 'devamsizlik_gir') {
        try {
            $db->prepare("INSERT INTO devamsizlik (ogrenci_id, tarih, durum) VALUES (?,?,?)")
               ->execute([(int)$_POST['ogrenci_id'], $_POST['tarih'], $_POST['durum']]);
            $success = "Devamsızlık kaydedildi.";
        } catch (PDOException $e) { $error = "Hata oluştu."; }
    }
}

// SİLME İŞLEMLERİ
if (isset($_GET['del_ogr'])) { $db->prepare("DELETE FROM ogrenciler WHERE id=?")->execute([(int)$_GET['del_ogr']]); header("Location: teacher_dashboard.php"); exit; }
if (isset($_GET['del_ders'])) { $db->prepare("DELETE FROM dersler WHERE id=?")->execute([(int)$_GET['del_ders']]); header("Location: teacher_dashboard.php"); exit; }
if (isset($_GET['del_dev'])) { $db->prepare("DELETE FROM devamsizlik WHERE id=?")->execute([(int)$_GET['del_dev']]); header("Location: teacher_dashboard.php"); exit; }

$page_title = "Öğretmen Paneli";
include 'includes/header.php';

$students = $db->query("SELECT * FROM ogrenciler ORDER BY sinif, ad")->fetchAll();
$courses = $db->query("SELECT * FROM dersler ORDER BY ders_adi")->fetchAll();
$grades = $db->query("SELECT n.*, o.ad, o.soyad, o.sinif, d.ders_adi FROM notlar n JOIN ogrenciler o ON n.ogrenci_id=o.id JOIN dersler d ON n.ders_id=d.id ORDER BY o.sinif, o.ad, d.ders_adi")->fetchAll();
$attendance = $db->query("SELECT d.*,o.ad,o.soyad,o.sinif FROM devamsizlik d JOIN ogrenciler o ON d.ogrenci_id=o.id ORDER BY d.tarih DESC LIMIT 50")->fetchAll();
?>

<?php if(isset($success)): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
<?php if(isset($error)): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

<!-- ÖĞRENCİ YÖNETİMİ -->
<div id="ogrenciler" style="background:#fff; padding:15px; border-radius:5px; margin-bottom:20px; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
    <h3 style="border-bottom:1px solid #ddd; padding-bottom:10px; margin-bottom:15px;">Öğrenci Yönetimi</h3>
    <div class="flex-row">
        <div class="flex-1 border" style="padding:15px;">
            <h4>Yeni Öğrenci Ekle</h4>
            <form method="POST"><input type="hidden" name="islem" value="ogrenci_ekle">
                <input type="text" name="ogr_no" class="form-control" required placeholder="Öğrenci No" style="margin-bottom:5px;">
                <input type="text" name="ad" class="form-control" required placeholder="Ad" style="margin-bottom:5px;">
                <input type="text" name="soyad" class="form-control" required placeholder="Soyad" style="margin-bottom:5px;">
                <input type="text" name="sinif" class="form-control" required placeholder="Sınıf (Örn: 11-A)" style="margin-bottom:5px;">
                <input type="password" name="sifre" class="form-control" required placeholder="Şifre" style="margin-bottom:5px;">
                <button class="btn btn-primary btn-sm">Ekle</button>
            </form>
        </div>
        <div class="flex-2 border" style="padding:15px; max-height:250px; overflow-y:auto;">
            <h4>Öğrenci Listesi</h4>
            <table class="table" style="font-size:0.85rem;">
                <thead><tr><th>No</th><th>Ad Soyad</th><th>Sınıf</th><th>Sil</th></tr></thead>
                <tbody>
                    <?php foreach($students as $s): ?>
                    <tr>
                        <td><?= htmlspecialchars($s['ogr_no']) ?></td>
                        <td><?= htmlspecialchars($s['ad'].' '.$s['soyad']) ?></td>
                        <td><?= htmlspecialchars($s['sinif']) ?></td>
                        <td><a href="?del_ogr=<?= $s['id'] ?>" class="text-danger" onclick="return confirm('Emin misiniz?')"><i class="fa fa-trash"></i></a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- DERS YÖNETİMİ -->
<div id="dersler" style="background:#fff; padding:15px; border-radius:5px; margin-bottom:20px; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
    <h3 style="border-bottom:1px solid #ddd; padding-bottom:10px; margin-bottom:15px;">Ders Yönetimi</h3>
    <div class="flex-row">
        <div class="flex-1 border" style="padding:15px;">
            <h4>Yeni Ders Ekle</h4>
            <form method="POST"><input type="hidden" name="islem" value="ders_ekle">
                <input type="text" name="ders_adi" class="form-control" required placeholder="Ders Adı" style="margin-bottom:5px;">
                <button class="btn btn-primary btn-sm">Ekle</button>
            </form>
        </div>
        <div class="flex-2 border" style="padding:15px; max-height:200px; overflow-y:auto;">
            <h4>Ders Listesi</h4>
            <table class="table" style="font-size:0.85rem;">
                <thead><tr><th>Ders Adı</th><th>Sil</th></tr></thead>
                <tbody>
                    <?php foreach($courses as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['ders_adi']) ?></td>
                        <td><a href="?del_ders=<?= $c['id'] ?>" class="text-danger" onclick="return confirm('Emin misiniz?')"><i class="fa fa-trash"></i></a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- NOT VE DEVAMSIZLIK -->
<div class="flex-row">
    <!-- NOTLAR -->
    <div id="notlar" class="flex-1" style="background:#fff; padding:15px; border-radius:5px; margin-bottom:20px; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
        <h3 style="border-bottom:1px solid #ddd; padding-bottom:10px; margin-bottom:15px;">Not Girişi</h3>
        <form method="POST" style="font-size:0.9rem;"><input type="hidden" name="islem" value="not_gir">
            <select name="ogrenci_id" class="form-control" required style="margin-bottom:5px;">
                <option value="">Öğrenci Seç...</option>
                <?php foreach($students as $s): ?><option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['sinif'].' - '.$s['ad'].' '.$s['soyad']) ?></option><?php endforeach; ?>
            </select>
            <select name="ders_id" class="form-control" required style="margin-bottom:10px;">
                <option value="">Ders Seç...</option>
                <?php foreach($courses as $c): ?><option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['ders_adi']) ?></option><?php endforeach; ?>
            </select>
            <div style="display:flex; gap:5px; margin-bottom:5px;">
                <input type="number" name="vize1" class="form-control" min="0" max="100" step="0.01" placeholder="Vize 1">
                <input type="number" name="vize2" class="form-control" min="0" max="100" step="0.01" placeholder="Vize 2">
            </div>
            <div style="display:flex; gap:5px; margin-bottom:10px;">
                <input type="number" name="vize3" class="form-control" min="0" max="100" step="0.01" placeholder="Vize 3">
                <input type="number" name="final" class="form-control" min="0" max="100" step="0.01" placeholder="Final">
            </div>
            <button class="btn btn-primary btn-sm w-100">Notu Kaydet / Güncelle</button>
        </form>
        <hr>
        <div style="max-height:250px; overflow-y:auto;">
            <table class="table" style="font-size:0.75rem;">
                <thead><tr><th>Öğr</th><th>Ders</th><th>Ort</th></tr></thead>
                <tbody>
                    <?php foreach($grades as $g): ?>
                    <tr>
                        <td><?= htmlspecialchars($g['ad'].' '.$g['soyad']) ?></td>
                        <td><?= htmlspecialchars($g['ders_adi']) ?></td>
                        <td><?= $g['ortalama'] !== null ? number_format($g['ortalama'],1) : '-' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- DEVAMSIZLIK -->
    <div id="devamsizlik" class="flex-1" style="background:#fff; padding:15px; border-radius:5px; margin-bottom:20px; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
        <h3 style="border-bottom:1px solid #ddd; padding-bottom:10px; margin-bottom:15px;">Devamsızlık Girişi</h3>
        <form method="POST" style="font-size:0.9rem;"><input type="hidden" name="islem" value="devamsizlik_gir">
            <select name="ogrenci_id" class="form-control" required style="margin-bottom:5px;">
                <option value="">Öğrenci Seç...</option>
                <?php foreach($students as $s): ?><option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['sinif'].' - '.$s['ad'].' '.$s['soyad']) ?></option><?php endforeach; ?>
            </select>
            <input type="date" name="tarih" class="form-control" required value="<?= date('Y-m-d') ?>" style="margin-bottom:5px;">
            <select name="durum" class="form-control" required style="margin-bottom:10px;">
                <option value="Tam">Tam Gün</option>
                <option value="Yarim">Yarım Gün</option>
            </select>
            <button class="btn btn-primary btn-sm w-100">Devamsızlık Kaydet</button>
        </form>
        <hr>
        <div style="max-height:250px; overflow-y:auto;">
            <table class="table" style="font-size:0.75rem;">
                <thead><tr><th>Tarih</th><th>Öğr</th><th>Durum</th><th>Sil</th></tr></thead>
                <tbody>
                    <?php foreach($attendance as $a): ?>
                    <tr>
                        <td><?= date('d.m', strtotime($a['tarih'])) ?></td>
                        <td><?= htmlspecialchars($a['ad'].' '.$a['soyad']) ?></td>
                        <td><?= $a['durum']=='Tam'?'Tam':'Yarım' ?></td>
                        <td><a href="?del_dev=<?= $a['id'] ?>" class="text-danger" onclick="return confirm('Emin misiniz?')"><i class="fa fa-trash"></i></a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
