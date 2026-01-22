<?php require __DIR__.'/_common.php';
login_required();
$me = current_user();

/* 投稿 */
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['body'])) {
  $img = null;
  if (!empty($_POST['image_base64'])) $img = save_base64_image($_POST['image_base64']);
  $st = db()->prepare('INSERT INTO bbs_entries(user_id,body,image_filename) VALUES(:u,:b,:i)');
  $st->execute([':u'=>$me['id'], ':b'=>$_POST['body'], ':i'=>$img]);
  header('Location:/timeline.php'); exit;
}

/* タイムライン（まずは全件） */
$st = db()->query('SELECT b.*, u.name AS user_name, u.icon_filename AS user_icon_filename
                     FROM bbs_entries b JOIN users u ON b.user_id=u.id
                    ORDER BY b.created_at DESC');
$entries = $st->fetchAll();
?>
<div>
    現在 <?= e($me['name']) ?> (ID: <?= e($me['id']) ?>) /
    <a href="/users.php">会員一覧</a> /
    <a href="/setting/index.php">設定</a> /
    <a href="/logout.php">ログアウト</a>
</div>

<h2>投稿</h2>
<form method="post">
    <textarea name="body" required style="width:100%;height:6em"></textarea><br>
    <input type="file" accept="image/*" id="imageInput"><br>
    <input type="hidden" name="image_base64" id="imageBase64Input">
    <canvas id="imageCanvas" style="display:none"></canvas>
    <button>送信</button>
</form>
<hr>

<h2>タイムライン</h2>
<?php foreach($entries as $e): ?>
<div style="border-bottom:1px solid #ccc; padding:.6em 0;">
    <div>
        <a href="/profile.php?user_id=<?= $e['user_id'] ?>">
            <?php if(!empty($e['user_icon_filename'])): ?>
            <img src="/image/<?= e($e['user_icon_filename']) ?>"
                style="height:1.6em;width:1.6em;border-radius:50%;object-fit:cover;vertical-align:middle;">
            <?php endif; ?>
            <?= e($e['user_name']) ?> (ID: <?= e($e['user_id']) ?>)
        </a>
        ｜ <?= e($e['created_at']) ?>
    </div>
    <div><?= nl2br(e($e['body'])) ?></div>
    <?php if(!empty($e['image_filename'])): ?>
    <div><img src="/image/<?= e($e['image_filename']) ?>" style="max-height:10em"></div>
    <?php endif; ?>
</div>
<?php endforeach; ?>

<script>
const input = document.getElementById('imageInput');
input.addEventListener('change', () => {
    const f = input.files[0];
    if (!f || !f.type.startsWith('image/')) return;
    const cv = document.getElementById('imageCanvas'),
        ctx = cv.getContext('2d'),
        img = new Image(),
        fr = new FileReader();
    fr.onload = () => {
        img.onload = () => {
            const mw = 1000,
                w = img.naturalWidth,
                h = img.naturalHeight;
            let nw = w,
                nh = h;
            if (w > mw || h > mw) {
                if (w > h) {
                    nw = mw;
                    nh = Math.round(h * mw / w);
                } else {
                    nh = mw;
                    nw = Math.round(w * mw / h);
                }
            }
            cv.width = nw;
            cv.height = nh;
            ctx.drawImage(img, 0, 0, nw, nh);
            document.getElementById('imageBase64Input').value = cv.toDataURL('image/png');
        };
        img.src = fr.result;
    };
    fr.readAsDataURL(f);
});
</script>