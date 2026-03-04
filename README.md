⚖️ منصة المحاماة الحرّة (Get Lawyer App)

منصة متكاملة بنظام العمل الحر (Freelance) تربط بين العملاء والمحامين/مكاتب المحاماة. يتيح النظام للعملاء نشر قضاياهم وتلقي عروض أسعار من محامين موثقين، مع وجود لوحة تحكم إدارية لإدارة التوثيق والموظفين.

🚀 المميزات الرئيسية (Core Features)

نظام أدوار متعدد (Multi-Role System): (أدمن، موظف منصة، عميل، محامي، مكتب محاماة).

توثيق مزودي الخدمة: نظام رفع مستندات (هوية، رخصة مزاولة) ومراجعتها من الإدارة.

إدارة القضايا: دورة حياة كاملة للقضية (نشر، تقديم عروض، قبول، تنفيذ، إغلاق).

نظام الإشعارات: تنبيهات فورية لكل حدث (عرض جديد، قبول عرض، تحديث حالة التوثيق).

لوحة تحكم إدارية: إحصائيات شاملة للمنصة وإدارة طلبات التوثيق والموظفين.

🛠 التكنولوجيا المستخدمة (Tech Stack)

Framework: Laravel 12 (Latest Version)

Database: MySQL

Authentication: Laravel Sanctum (Token-based)

Programming Language: PHP 8.3+

🏗 هيكلية قاعدة البيانات (Database Schema)

Users: لتخزين جميع أنواع المستخدمين.

Provider Profiles: بيانات التوثيق والسيرة الذاتية للمحامين والمكاتب.

Legal Cases: بيانات القضايا المنشورة من قبل العملاء.

Offers: العروض المقدمة من المحامين على القضايا.

Notifications: نظام إشعارات Laravel المخزن في قاعدة البيانات.

📡 قائمة المسارات (API Endpoints)

1. المسارات العامة (Public)

Method

Endpoint

Description

POST

/api/register

تسجيل حساب جديد (عميل/محامي/مكتب)

POST

/api/login

تسجيل الدخول والحصول على التوكن

2. مسارات العميل (Client)

Method

Endpoint

Description

GET

/api/client/cases

عرض جميع قضايا العميل الحالي

POST

/api/client/cases

نشر قضية قانونية جديدة

GET

/api/client/cases/{id}/offers

عرض العروض المقدمة على قضية معينة

POST

/api/client/offers/{id}/accept

قبول عرض محامي محدد وبدء التنفيذ

PATCH

/api/client/cases/{id}/status

تحديث حالة القضية (مكتملة/غير محلولة)

3. مسارات المحامي/المكتب (Provider)

Method

Endpoint

Description

POST

/api/provider/upload-docs

رفع مستندات التوثيق (هوية، رخصة، إلخ)

GET

/api/provider/cases

تصفح القضايا المتاحة للتقديم (قيد الانتظار)

POST

/api/provider/cases/{id}/offers

تقديم عرض سعر ومقترح على قضية

4. مسارات الإدارة (Admin & Staff)

Method

Endpoint

Description

GET

/api/admin/dashboard

إحصائيات شاملة للمنصة

GET

/api/admin/pending-lawyers

عرض طلبات التوثيق التي تنتظر المراجعة

POST

/api/admin/verify-lawyer/{id}

قبول أو رفض توثيق محامي مع الملاحظات

POST

/api/admin/add-staff

إضافة موظف خدمة عملاء للمنصة (للأدمن فقط)

⚙️ التثبيت والتشغيل (Installation)

تحميل المشروع:

git clone <repository_url>
cd get-lawyer-app


تثبيت المكتبات:

composer install


إعداد ملف البيئة:

cp .env.example .env
# قم بتعديل بيانات قاعدة البيانات في ملف .env


توليد مفتاح التطبيق وعمل الهجرة:

php artisan key:generate
php artisan migrate


ربط ملفات التخزين (للصور):

php artisan storage:link


تشغيل المشروع:

php artisan serve


📝 ملاحظات إضافية

جميع الطلبات المحمية تتطلب Authorization: Bearer {token} في الـ Header.

يفضل استخدام Postman لتجربة المسارات.

النظام يدعم التوثيق الإلزامي للمحامين قبل السماح لهم بتقديم أي عروض.

📩 للتواصل والاستفسار

إذا كان لديك أي استفسار حول المشروع، يمكنك التواصل مع Abdalrhman عبر:

GitHub: @AbdalrhmanAbdoAlhade

Email: abdo.king22227@gmail.com

تم التطوير بواسطة: Abdalrhman