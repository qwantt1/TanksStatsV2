<article class="product-card<?= $tank['in_game'] ? '' : ' product-card--out-of-stock' ?>">
    <div class="product-card__media">
        <img class="product-card__image" src="<?= htmlspecialchars(tank_image($tank)) ?>" alt="<?= htmlspecialchars($tank['name']) ?>" loading="lazy">
        <form class="product-card__fav" method="post" action="favorites.php">
            <input type="hidden" name="action" value="toggle">
            <input type="hidden" name="tank_id" value="<?= (int)$tank['id'] ?>">
            <input type="hidden" name="back" value="<?= htmlspecialchars($back) ?>">
            <?php if (in_array($tank['id'], $favoriteIds)): ?>
                <button class="heart heart--active" type="submit" title="Убрать из избранного" aria-label="Убрать из избранного">
                    <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20.4l-1.4-1.3C5.6 14.6 2.5 11.8 2.5 8.3 2.5 5.5 4.7 3.3 7.5 3.3c1.6 0 3.2.8 4.5 2 1.3-1.2 2.9-2 4.5-2 2.8 0 5 2.2 5 5 0 3.5-3.1 6.3-8.1 10.8L12 20.4z"/></svg>
                </button>
            <?php else: ?>
                <button class="heart" type="submit" title="Добавить в избранное" aria-label="Добавить в избранное">
                    <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20.4l-1.4-1.3C5.6 14.6 2.5 11.8 2.5 8.3 2.5 5.5 4.7 3.3 7.5 3.3c1.6 0 3.2.8 4.5 2 1.3-1.2 2.9-2 4.5-2 2.8 0 5 2.2 5 5 0 3.5-3.1 6.3-8.1 10.8L12 20.4z"/></svg>
                </button>
            <?php endif; ?>
        </form>
    </div>

    <div class="product-card__body">
        <div>
            <h2 class="product-card__title">
                <a href="tank.php?id=<?= (int)$tank['id'] ?>"><?= htmlspecialchars($tank['name']) ?></a>
            </h2>
            <p class="product-card__meta">
                <?= htmlspecialchars($tank['nation_name'] ?? 'Без нации') ?><span class="product-card__category"><?= htmlspecialchars($tank['type_name'] ?? 'Без типа') ?></span>
            </p>
        </div>

        <?php if (!empty($tank['changed'])): ?>
            <p class="product-card__changed"><span class="badge badge--warning">ТТХ изменились</span></p>
        <?php endif; ?>

        <dl class="stats">
            <div class="stats__item">
                <dt class="stats__label">Прочность</dt>
                <dd class="stats__value"><?= number_format($tank['hp'], 0, '.', ' ') ?></dd>
            </div>
            <div class="stats__item">
                <dt class="stats__label">Урон</dt>
                <dd class="stats__value"><?= number_format($tank['damage'], 0, '.', ' ') ?></dd>
            </div>
            <div class="stats__item">
                <dt class="stats__label">Пробитие</dt>
                <dd class="stats__value"><?= (int)$tank['penetration'] ?> мм</dd>
            </div>
            <div class="stats__item">
                <dt class="stats__label">Скорость</dt>
                <dd class="stats__value"><?= (int)$tank['speed'] ?> км/ч</dd>
            </div>
        </dl>

        <p class="product-card__price"><?= number_format(dpm($tank), 0, '.', ' ') ?> <span class="product-card__price-unit">урона в минуту</span></p>

        <div class="product-card__bottom">
            <p class="product-card__stock">
                <?php if ($tank['in_game']): ?>
                    <span class="badge badge--success">Есть в игре</span>
                <?php else: ?>
                    <span class="badge badge--error">Выведен из игры</span>
                <?php endif; ?>
            </p>
            <?php if (in_array($tank['id'], $compareIds)): ?>
                <a class="btn btn--small btn--outline" href="compare.php">В сравнении</a>
            <?php else: ?>
                <form method="post" action="compare.php">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="tank_id" value="<?= (int)$tank['id'] ?>">
                    <input type="hidden" name="back" value="<?= htmlspecialchars($back) ?>">
                    <button class="btn btn--small" type="submit">Сравнить</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</article>
