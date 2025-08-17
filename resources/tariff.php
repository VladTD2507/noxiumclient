<div class="container">
    <a href="#tariff" class="about-tariff-scroll">
        <img src="assets/icons/ic-arrow.svg" alt="иконка скролла">
    </a>
    <div id="tariff" class="about-tariff-section">
        <div class="about-tariff-section-desc">
            <h3 class="about-tariff-section-headline">Тарифы</h3>
            <p class="about-tariff-section-desc-subtitle">Оформить тарифный план можно в личном кабинете.</p>
        </div>
        <div class="about-tariff-card-list">
            <?php
            try {
                $query = "SELECT productId, productName, productPrice, productDir, productOldPrice FROM Market";
                $stmt = $conn->prepare($query);
                $stmt->execute();
                $stmt->store_result();
                $stmt->bind_result($tariffId, $tariffName, $tariffPrice, $productDir, $tariffOldPrice);

                if ($stmt->num_rows > 0) {
                    while ($stmt->fetch()) {
                        echo '<a href="cabinet" class="about-tariff-card aos-init aos-animate" data-aos="fade-up" data-aos-delay="100" data-aos-once="true">';
                        echo '<img class="about-tariff-card-image" src="' . $productDir . '" alt="изображение тариф ' . htmlspecialchars($tariffName) . '">';
                        echo '<h4 class="about-tariff-card-period">' . htmlspecialchars($tariffName) . '</h4>';
                        echo '<span class="about-tariff-card-price">' . htmlspecialchars($tariffPrice) . ' ₽';
                        if ($tariffOldPrice) {
                            echo ' <span class="about-tariff-card-old-price">' . htmlspecialchars($tariffOldPrice) . ' ₽</span>';
                        }
                        echo '</span>';
                        echo '</a>';
                    }
                } else {
                    echo "No tariffs found in the database.";
                }

                $stmt->close();
            } catch (Exception $e) {
                echo "Error: " . $e->getMessage();
            }
            ?>
        </div>
    </div>
</div>