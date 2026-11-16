<div class="page-header">
    <div>
        <h1 class="page-title">Feedback</h1>
        <p class="page-subtitle">Tell us how we're doing</p>
    </div>
</div>

<?php if (!empty($msg)): ?>
    <div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800"><?= e($msg) ?></div>
<?php endif; ?>

<div class="card max-w-xl">
    <div class="card-body">
        <form method="post" class="space-y-4">
            <?= csrf_field() ?>
            <div>
                <label class="form-label">Order (optional)</label>
                <select class="form-select" name="order_id">
                    <option value="">— General feedback —</option>
                    <?php foreach ($orders as $o): ?>
                        <option value="<?= e($o['id']) ?>"><?= e($o['order_number']) ?> (<?= e(dt_fmt($o['created_at'],'d M')) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="form-label">Rating</label>
                <select class="form-select" name="rating" required>
                    <option value="5">★★★★★ Excellent</option>
                    <option value="4">★★★★☆ Good</option>
                    <option value="3">★★★☆☆ OK</option>
                    <option value="2">★★☆☆☆ Poor</option>
                    <option value="1">★☆☆☆☆ Bad</option>
                </select>
            </div>
            <div>
                <label class="form-label">Comment</label>
                <textarea class="form-textarea" name="comment" rows="4" placeholder="Tell us what you loved or what we can improve..."></textarea>
            </div>
            <button class="btn btn-primary w-full !py-3">Submit Feedback</button>
        </form>
    </div>
</div>
