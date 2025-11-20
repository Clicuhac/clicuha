# 🎨 STYLE_GUIDE.md --- Clicuha

Офіційний гайд по стилю для UI/UX Clicuha.\
Мета --- зробити вигляд сайту єдиним, охайним та професійним.

------------------------------------------------------------------------

# 1️⃣ Кольори

    --color-primary:   #3A7AFE;
    --color-accent:    #FFB800;
    --color-success:   #28C76F;
    --color-danger:    #EA5455;
    --color-bg:        #F7F7F7;
    --color-text:      #1E1E1E;

Правила: - усі кольори тільки через CSS-змінні\
- жодних "#ff00ff" у коді\
- у майбутньому --- темна тема на основі тих самих змінних

------------------------------------------------------------------------

# 2️⃣ Типографіка

## Шрифти:

-   Основний: **Inter**, **Roboto**, або інший clean sans-serif\
-   Розміри:

```{=html}
<!-- -->
```
    .fs-12 { font-size: 12px; }
    .fs-14 { font-size: 14px; }
    .fs-16 { font-size: 16px; }
    .fs-18 { font-size: 18px; }
    .fs-24 { font-size: 24px; }

## Заголовки:

    h1 { font-size: 28px; font-weight: 700; }
    h2 { font-size: 22px; font-weight: 600; }
    h3 { font-size: 18px; font-weight: 600; }

------------------------------------------------------------------------

# 3️⃣ Відступи

Усі відступи робимо через класи:

    .mt-10 { margin-top: 10px; }
    .mb-20 { margin-bottom: 20px; }
    .p-10  { padding: 10px; }
    .p-20  { padding: 20px; }

Жодних рандомних inline-стилів типу `style="padding:13px"`.

------------------------------------------------------------------------

# 4️⃣ Кнопки

Базова кнопка:

    .btn {
      display: inline-block;
      padding: 10px 18px;
      border-radius: 6px;
      background: var(--color-primary);
      color: #fff;
      cursor: pointer;
    }
    .btn:hover {
      background: #2b67d7;
    }

Варіації:

    .btn-accent  { background: var(--color-accent); }
    .btn-danger  { background: var(--color-danger); }
    .btn-outline { background: transparent; border: 1px solid var(--color-primary); color: var(--color-primary); }

------------------------------------------------------------------------

# 5️⃣ Форми

    .input {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 6px;
    }
    .input:focus {
      border-color: var(--color-primary);
    }
    .checkbox {
      transform: scale(1.2);
    }

------------------------------------------------------------------------

# 6️⃣ Модульність CSS

CSS ділимо так:

    /assets/css/main.css
    /assets/css/forms.css
    /assets/css/buttons.css
    /assets/css/admin.css
    /assets/css/colors.css

------------------------------------------------------------------------

# 7️⃣ Зображення

Правила: - завжди `webp`, якщо можливо\
- імена --- у стилі `nickname-icon.webp`\
- ніколи не грузимо 3--5 МБ картинки

------------------------------------------------------------------------

# ✔️ 8️⃣ Висновок

Цей гайд --- основа візуальної консистентності Clicuha.\
Його можна розширювати з розвитком дизайну.
