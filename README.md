# 📖 نظام تسميع القرآن الكريم
## جمعية المحافظة على القرآن الكريم

نظام إلكتروني متكامل لمتابعة وتسميع القرآن الكريم للطلاب الحفظة.

---

## 🚀 متطلبات التشغيل

- PHP 8.1 أو أحدث
- MySQL 8.0 أو MariaDB 10.3+
- Composer 2.x
- Node.js 16+ (اختياري)
- Web Server: Apache أو Nginx

---

## 📦 خطوات التثبيت

### 1. استخراج الملفات
```bash
unzip quran-system.zip -d quran-system
cd quran-system
```

### 2. تثبيت المكتبات
```bash
composer install
```

### 3. إعداد ملف البيئة
```bash
cp .env.example .env
php artisan key:generate
```

### 4. إعداد قاعدة البيانات
عدّل ملف `.env` وأدخل بيانات قاعدة البيانات:
```
DB_DATABASE=quran_system
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 5. إنشاء قاعدة البيانات والجداول
```bash
php artisan migrate
php artisan db:seed
```

### 6. إعداد مجلد التخزين
```bash
php artisan storage:link
chmod -R 775 storage bootstrap/cache
```

### 7. تشغيل الخادم المحلي
```bash
php artisan serve
```

ثم افتح: http://localhost:8000

---

## 🔑 بيانات الدخول الافتراضية

| المستخدم | البريد الإلكتروني | كلمة المرور | الصلاحية |
|---|---|---|---|
| مدير النظام | admin@quran.com | Admin@123 | مدير |
| أحمد محمد | ahmed@quran.com | Super@123 | مشرف |
| محمود علي | mahmoud@quran.com | Super@123 | مشرف |
| عبدالله أحمد | student1@quran.com | Student@123 | طالب |
| والد عبدالله | parent1@quran.com | Parent@123 | ولي أمر |

> ⚠️ **مهم**: غيّر كلمات المرور فور تثبيت النظام!

---

## 🏗️ هيكل النظام

```
quran-system/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/          # بوابة الإدارة
│   │   │   ├── Supervisor/     # بوابة المشرف
│   │   │   ├── Student/        # بوابة الطالب
│   │   │   └── ParentPortal/   # بوابة ولي الأمر
│   │   └── Middleware/
│   └── Models/                 # النماذج
├── database/
│   ├── migrations/             # جداول قاعدة البيانات
│   └── seeders/                # البيانات الأولية
├── resources/views/
│   ├── admin/                  # واجهة الإدارة
│   ├── supervisor/             # واجهة المشرف
│   ├── student/                # واجهة الطالب
│   └── parent/                 # واجهة ولي الأمر
└── routes/
    └── web.php                 # المسارات
```

---

## ✨ المميزات الرئيسية

### 👤 بوابة الإدارة
- إدارة كاملة للمستخدمين (طلاب، مشرفين، أولياء أمور)
- إدارة المستويات والصلاحيات
- تقارير شاملة للجمعية
- مراقبة الإنذارات

### 👨‍🏫 بوابة المشرف
- **تسجيل الحضور والغياب** مع وقت التأخير
- **التسميع بأنواعه:**
  - حفظ جديد (حسب مستوى الطالب)
  - مراجعة
  - حفظ غير متقن (5، 10، 15، 20 صفحة)
  - سرد (جزء كامل)
  - مفصل
- **نظام الألوان التلقائي:**
  - 🟢 أخضر: 0 أخطاء أو ≤2 تردد
  - 🟠 برتقالي: خطأ واحد أو 3-5 ترددات
  - 🔴 أحمر: خطآن
  - ⚫ أسود (راسب): خطآن + تردد
- إصدار تقارير PDF
- إصدار إنذارات رسمية

### 📚 بوابة الطالب
- متابعة التقدم الشخصي
- سجل جلسات التسميع
- سجل الحضور
- صفحة طرق الحفظ المتقن مع مؤقت ذكي

### 👨‍👩‍👦 بوابة ولي الأمر
- متابعة أداء الأبناء
- استلام الإنذارات
- تقارير مفصلة

---

## 📊 نظام المفاصل والعلامات

| المفصل | علامة البداية | خصم الخطأ | خصم التردد |
|---|---|---|---|
| 3 و6 | 90% | -3 علامات | كل 3 ترددات = -1 |
| 10 و15 | 80% | -3 علامات | كل 3 ترددات = -1 |
| 20 و25 و30 | 75% | -3 علامات | كل 3 ترددات = -1 |

---

## 🔧 إعداد خادم الإنتاج (Nginx)

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/quran-system/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    charset utf-8;
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
}
```

---

## 📄 الترخيص

هذا النظام مطوّر خصيصاً لجمعية المحافظة على القرآن الكريم.
جميع الحقوق محفوظة © 2024

---

## 📞 الدعم الفني

للدعم الفني والاستفسارات، يرجى التواصل مع مطور النظام.
