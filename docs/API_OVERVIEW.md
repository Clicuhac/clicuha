# 🌐 API & Integration Overview --- Clicuha

Цей документ описує майбутню структуру API Clicuha, базові принципи,
формати запитів та потенційні точки інтеграції.\
API планується як доповнення до PHP-роутів для мобільних застосунків,
AI-агентів та сторонніх сервісів.

------------------------------------------------------------------------

## 📌 1. Загальна концепція API

-   API буде у форматі **REST** (JSON-відповіді).

-   Базовий URL (план):

        https://clicuha.com/api/

-   Кожен endpoint повертає:

    ``` json
    {
      "status": "success" | "error",
      "data": { ... },
      "message": "Текст для дебагу або користувача"
    }
    ```

------------------------------------------------------------------------

## 📌 2. Плановані групи endpoint'ів

### **1) Nicknames**

    GET    /api/nicknames
    GET    /api/nicknames/{id}
    POST   /api/nicknames
    PUT    /api/nicknames/{id}
    DELETE /api/nicknames/{id}

### **2) Comments**

    GET    /api/nicknames/{id}/comments
    POST   /api/comments
    DELETE /api/comments/{id}

### **3) Users**

    POST   /api/login
    POST   /api/logout
    GET    /api/profile

### **4) Likes**

    POST   /api/like
    DELETE /api/like/{id}

------------------------------------------------------------------------

## 📌 3. Формат даних

### **POST /api/nicknames**

``` json
{
  "title": "Фаза",
  "description": "Дуже хитра клікуха",
  "anonymous": true
}
```

### **POST /api/comments**

``` json
{
  "nickname_id": 12,
  "text": "Класна клікуха!"
}
```

------------------------------------------------------------------------

## 📌 4. Авторизація API

### **JWT або Session Tokens**

Планується: - видача токена при логіні; - API-запити з заголовком:
`Authorization: Bearer <token>`

------------------------------------------------------------------------

## 📌 5. Error Handling

При помилках API повертає:

``` json
{
  "status": "error",
  "message": "Invalid nickname id",
  "code": 400
}
```

------------------------------------------------------------------------

## 📌 6. Майбутні можливості

### **1) Webhooks**

-   нотифікація про нові клікухи;
-   про активність у дуелях;
-   для інтеграції з Telegram.

### **2) AI endpoints**

    POST /api/ai/generate
    POST /api/ai/chat

### **3) Public GraphQL API**

Для розробників, які хочуть робити сторонні клієнти.

------------------------------------------------------------------------

## ✔️ 7. Висновок

API --- це шлях до мобільного застосунку, до AI-інтеграції та до
масштабування.\
Документ буде доповнюватися у міру появи нових endpoint'ів.
