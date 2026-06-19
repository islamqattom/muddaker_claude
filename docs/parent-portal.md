# بوابة ولي الأمر — دليل شامل

> **المسار في النظام**: `/parent/*`  ·  **الدور المطلوب**: `parent`  ·  **الـ Middleware**: `auth`, `role:parent`

---

## 1. نظرة عامة

بوابة ولي الأمر هي واجهة مخصصة لآباء وأمهات الطلاب لمتابعة تقدم أبنائهم في حفظ القرآن الكريم داخل الجمعية. لا يستطيع ولي الأمر تعديل أي بيانات — كل الصفحات **قراءة فقط** ما عدا صفحة الملف الشخصي (تعديل الاسم/كلمة المرور).

### الصفحات المتاحة (12 صفحة)

| # | الصفحة | المسار | المتحكم | الوصف المختصر |
|---|---|---|---|---|
| 1 | لوحة المعلومات | `/parent/dashboard` | `DashboardController@index` | ملخص عام عن جميع الأبناء |
| 2 | تفاصيل ابن | `/parent/student/{id}` | `DashboardController@studentDetail` | تفاصيل شاملة لابن محدد |
| 3 | جلسات التسميع | `/parent/sessions` | `SessionController@index` | قائمة بكل جلسات التسميع |
| 4 | جلسة تفصيلية | `/parent/sessions/{id}` | `SessionController@show` | تفاصيل جلسة تسميع واحدة |
| 5 | الحضور | `/parent/attendance` | `AttendanceController@index` | سجل الحضور والغياب |
| 6 | الإنذارات | `/parent/warnings` | `WarningController@index` | قائمة الإنذارات |
| 7 | إنذار تفصيلي | `/parent/warnings/{id}` | `WarningController@show` | تفاصيل إنذار محدد |
| 8 | التقارير | `/parent/reports` | `ReportController@index` | تقارير الأداء |
| 9 | الملف الشخصي | `/parent/profile` | `ProfileController@index` | عرض وتعديل البيانات |
| 10 | سجل الأجزاء | `/parent/student-parts` | `ParentStudentPartRecordController@index` | تقدم الأبناء في الأجزاء الثلاثين |
| 11 | يوم الهمة | `/parent/yawm-al-himma` | `YawmAlHimmaController@index` | جلسات التسميع الجماعية |
| 12 | الفواتير | `/parent/billing` | `BillingController@index` | الفواتير والمدفوعات |

> جميع المسارات محمية بـ `role:parent`. أي محاولة دخول من حساب مشرف أو طالب ستُحوَّل إلى خطأ 403.

---

## 2. لوحة المعلومات (Dashboard)

**المسار**: `/parent/dashboard`  
**الملف**: `resources/views/parent/dashboard/index.blade.php`

### ما تعرضه الصفحة

1. **شريط الترحيب**: اسم ولي الأمر + التاريخ الميلادي بالعربي.
2. **4 بطاقات إحصائية مجمّعة** (لكل الأبناء معاً):
   - **عدد الأبناء** — إجمالي عدد الطلاب المرتبطين بالحساب.
   - **حضور هذا الشهر** — مجموع أيام الحضور.
   - **جلسات التسميع** — إجمالي جلسات الشهر الحالي.
   - **إنذارات جديدة** — عدد الإنذارات غير المقروءة.
3. **دليل الألوان والتقييم** — مرجع ثابت يشرح معنى كل لون في تقييمات التسميع.
4. **بطاقة لكل ابن** — تعرض:
   - الاسم والمستوى (المستوى الحالي في الجمعية).
   - **الحضور هذا الشهر** (حاضر / غائب / متأخر / بعذر).
   - **التسميع هذا الشهر** (إجمالي / ناجح / راسب / نسبة النجاح).
   - **الإنذارات الأخيرة** (آخر 3 إنذارات جديدة).
   - زر **"عرض التفاصيل"** ينقل إلى `/parent/student/{id}`.

### دليل الألوان

| اللون | المعنى | الشرط |
|---|---|---|
| 🟢 أخضر — ممتاز | لم يخطئ أو تردد بحد أقصى ترددين |
| 🟠 برتقالي — جيد | خطأ واحد، أو من 3 إلى 5 ترددات |
| 🔴 أحمر — ضعيف | خطآن، أو ما يعادلهما من الترددات |
| ⚫ أسود — راسب | خطآن + تردد = رسوب |

### عتبات النجاح في اختبار المفصل

| نوع الاختبار | الحد الأدنى للنجاح |
|---|---|
| مفصل 3 و 6 أجزاء | 90% |
| مفصل 10 و 15 جزء | 80% |
| مفصل 20 و 25 و 30 جزء | 75% |

**صيغة الخصم**: كل خطأ = **-3 علامات**، وكل 3 ترددات = **-1 علامة**.

---

## 3. تفاصيل ابن محدد (Student Detail)

**المسار**: `/parent/student/{student}`  
**الملف**: `resources/views/parent/dashboard/student-detail.blade.php`

تعرض كل بيانات ابن واحد: معلومات شخصية، تقدم الحفظ، آخر الجلسات، الإحصائيات التفصيلية.

---

## 4. جلسات التسميع

### 4.1 قائمة الجلسات

**المسار**: `/parent/sessions`  
**الملف**: `resources/views/parent/sessions/index.blade.php`

- **فلاتر**: نوع الجلسة (مفصل / جديد / قديم / مراجعة / تثبيت)، التاريخ، الحالة.
- **الأعمدة**: التاريخ، النوع، الجزء، الحالة (ناجح/راسب)، الدرجة.
- شارة خاصة لتمييز **المفصل** (3/6، 10/15، 20/25/30).

### 4.2 تفاصيل جلسة

**المسار**: `/parent/sessions/{session}`  
**الملف**: `resources/views/parent/sessions/show.blade.php`

- الدرجة النهائية + درجة النجاح المطلوبة.
- قائمة الصفحات المسمّعة مع **شارة صعوبة الموضع** ولون التقييم لكل صفحة.
- ملاحظات المشرف.

---

## 5. الحضور والغياب

**المسار**: `/parent/attendance`  
**الملف**: `resources/views/parent/attendance/index.blade.php`

- عرض شهري أو نطاق تاريخي محدد.
- لكل يوم: **حاضر** / **غائب** / **متأخر** / **بعذر**.
- نسبة الحضور الشهرية.

---

## 6. الإنذارات

### 6.1 قائمة الإنذارات

**المسار**: `/parent/warnings`  
**الملف**: `resources/views/parent/warnings/index.blade.php`

### 6.2 تفاصيل إنذار

**المسار**: `/parent/warnings/{warning}`  
**الملف**: `resources/views/parent/warnings/show.blade.php`

- العنوان، النوع (حفظ/سلوك/غياب/مفصل)، تاريخ الإصدار.
- وصف المخالفة والإجراء المتخذ.

---

## 7. التقارير

**المسار**: `/parent/reports`  
**الملف**: `resources/views/parent/reports/index.blade.php`

تقارير قابلة للتصدير (PDF) عن أداء الأبناء على فترات محددة.

---

## 8. الملف الشخصي

**المسار**: `/parent/profile`  
**الملف**: `resources/views/parent/profile/index.blade.php`

**العمليات المتاحة** (الصفحة الوحيدة القابلة للتعديل):

| الإجراء | الطريقة | المسار |
|---|---|---|
| تحديث البيانات | `PUT` | `/parent/profile` |
| تغيير كلمة المرور | `PUT` | `/parent/profile/password` |

---

## 9. سجل الأجزاء (Student Parts Record)

**المسار الرئيسي**: `/parent/student-parts`  
**المسار التفصيلي**: `/parent/student-parts/{studentId}`  
**الملفات**:
- `resources/views/parent/student-parts/index.blade.php`
- `resources/views/parent/student-parts/show.blade.php`

### 9.1 الفهرس

شبكة عرض للأجزاء الثلاثين، مع ملخص حالة كل ابن في كل جزء.

### 9.2 التفاصيل

**الجدول الرئيسي** — صف لكل جزء، يحتوي:

| العمود | الوصف |
|---|---|
| رقم الجزء | من 1 إلى 30 |
| التسميع الجديد | هل أُنهي الجزء بالتسميع الجديد |
| التسميع القديم | القراءات القديمة |
| المراجعة | هل تمت مراجعة الجزء |
| التثبيت | هل ثُبّت الجزء |
| المفصل | نجوم تمثل عدد مرات النجاح في اختبار المفصل |

شارة النجمة (⭐) بجانب كل جزء يدل على إجادة اختبار المفصل لذلك الجزء.

---

## 10. يوم الهمة

**المسار**: `/parent/yawm-al-himma`  
**الملفات**:
- `resources/views/parent/yawm-al-himma/index.blade.php`
- `resources/views/parent/yawm-al-himma/show.blade.php`

صفحة مخصصة لجلسات التسميع الجماعية التي تُقام في المناسبات. تعرض قائمة الجلسات العامة وتُظهر مشاركة ابنك فيها.

---

## 11. الفواتير

**المسار**: `/parent/billing`  
**الملف**: `resources/views/parent/billing/index.blade.php`

- الفواتير الشهرية.
- حالة الدفع (مدفوعة / معلقة / متأخرة).
- المبالغ والتواريخ.

---

## 12. القائمة الجانبية

**الملف**: `resources/views/parent/partials/sidebar.blade.php`

روابط سريعة لكل الصفحات الـ 12. التصميم يستخدم نفس الثيم (الأخضر الداكن + الذهبي) كباقي البوابات.

---

## 13. المتحكمات (Controllers)

| المتحكم | المسار | المسؤولية |
|---|---|---|
| `App\Http\Controllers\ParentPortal\DashboardController` | `app/Http/Controllers/ParentPortal/DashboardController.php` | لوحة المعلومات + تفاصيل ابن |
| `App\Http\Controllers\ParentPortal\SessionController` | نفس المجلد | جلسات التسميع |
| `App\Http\Controllers\ParentPortal\AttendanceController` | نفس المجلد | الحضور |
| `App\Http\Controllers\ParentPortal\WarningController` | نفس المجلد | الإنذارات |
| `App\Http\Controllers\ParentPortal\ReportController` | نفس المجلد | التقارير |
| `App\Http\Controllers\ParentPortal\ProfileController` | نفس المجلد | الملف الشخصي |
| `App\Http\Controllers\ParentPortal\YawmAlHimmaController` | نفس المجلد | يوم الهمة |
| `App\Http\Controllers\ParentPortal\BillingController` | نفس المجلد | الفواتير |
| `App\Http\Controllers\ParentStudentPartRecordController` | `app/Http/Controllers/` | سجل الأجزاء (مختلف عن البقية لأنه يُستخدم في كل من بوابة الولي والمشرف) |

---

## 14. ملاحظات تقنية

- **الـ Authorization**: كل المسارات خلف `auth` + `role:parent`. ولي الأمر يرى **فقط** بياناته (الأبناء المرتبطين بحسابه عبر جدول `parent_student`). لا يوجد IDOR.
- **الـ Locale**: كل النصوص بالعربية، الاتجاه RTL.
- **التصميم**: إطار عمل CSS داخلي داخل كل Blade (مكوّنات `.welcome-wrapper`، `.stat-card`، `.guide-card`، `.student-card`… إلخ). يستخدم لوحة ألوان `#182B48` (أزرق داكن) + `#d4a030` (ذهبي).
- **الفلاش** (Toaster): يستخدم `toastr` لعرض رسائل النجاح/الخطأ.
- **التاريخ**: يظهر بالعربية عبر `toLocaleDateString('ar-SA', …)`.