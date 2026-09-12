# أساس معماري للدفع والشحن والـ Checkout

## النتيجة التنفيذية

المشروع يتبع فصلًا مناسبًا بين **Controller → Form Request → Use Case → Domain Contract → Infrastructure → Domain Exception → Central Error Handler**. المشكلة الأساسية لم تكن في وجود الطبقات، بل في حدود المعاملة الزمنية بين النظام المحلي والمزوّد الخارجي.

أصبح الـ checkout الآن معاملة محلية قصيرة، ثم خطوة دفع قابلة للاسترداد. لا يتم استدعاء بوابة الدفع داخل معاملة قاعدة البيانات. كما أصبح مفتاح idempotency للدفع يميّز بين إعادة طلب مكتمل وطلب آخر ما زال قيد التنفيذ.

> قاعدة العمل: لا توجد معاملة موزعة حقيقية بين قاعدة بيانات المتجر وبوابة دفع أو شركة شحن. استخدم **Saga**، والحالات الصريحة، وOutbox/Webhooks، والمصالحة الدورية بدل محاولة `2PC` مع مزوّد خارجي.

## ما تم اكتشافه وإصلاحه

| المجال | الخطر | الحالة بعد التعديل |
|---|---|---|
| سباق إنشاء الدفع | كان الطلب الثاني يعيد سجلًا بحالة `initiating` وكأن العملية اكتملت | أصبح يعيد السجل فقط للحالات المكتملة، ويعيد `409` عند وجود عملية جارية |
| استدعاء gateway داخل transaction | فشل قاعدة البيانات بعد نجاح الشحن الخارجي قد يخفي عملية دفع حقيقية | تم نقل الدفع خارج معاملة الطلب المحلية |
| استثناءات idempotency | كان الخطأ يصنف كفشل دفع عام | أضيف `PaymentInProgressException` وتصنيف `409 Conflict` |
| مفاتيح الدفع | الاعتماد على الفحص ثم الإنشاء فقط غير كافٍ | بقي القيد `UNIQUE` وقفل الصف هما مصدر الحماية الذري |
| Webhooks | لم توجد ذاكرة دائمة لمنع تكرار الحدث | أضيف جدول `payment_webhook_events` بقيد `(provider,event_id)` |
| تكامل المزوّد | توجد واجهة عامة لكنها لا تزال تحتاج Adapter حقيقيًا | يمر التكامل القادم عبر `PaymentGatewayInterface` دون إدخال اسم المزوّد إلى Domain |
| فشل الشحن داخل checkout | ما زال محليًا وقابلًا للـ rollback قبل الخروج من المعاملة | هذا هو السلوك الصحيح؛ استدعاء شركة الشحن الخارجية يجب أن يكون Saga لاحقة مشابهة للدفع |

## الحدود الصحيحة للطبقات

### Domain

يحتوي على الحالات، الانتقالات، Value Objects، Exceptions، وContracts فقط. لا يعرف Laravel أو Eloquent أو Stripe أو Paymob أو Aramex أو غيرها.

### Application

ينفذ حالات الاستخدام. ينسق بين المستودعات والعقود. يقرر متى يثبت claim، ومتى يعيد المحاولة، ومتى يحتاج إلى reconciliation.

### Infrastructure

يحتوي على Eloquent repositories، HTTP clients، توقيع Webhook، Queue jobs، وAdapters لكل مزوّد. يجب ألا يتسرب اسم المزوّد أو payload الخاص به إلى Domain.

### Presentation

يحتوي على Form Requests وControllers وResource/JSON mapping. يجب أن يعيد `409` للتعارضات، و`422` لبيانات الأعمال غير الصحيحة، و`503` عند تعطل مزوّد خارجي بعد إنشاء عملية محلية قابلة للاسترداد.

## نموذج الحالة المقترح

### Payment

`initiating → pending → paid → refunded`

وتوجد حالات فشل تشغيلية منفصلة:

`initiating → failed`

`pending → failed`

لا تحذف payment عند الفشل. وجود السجل يسمح بإعادة المحاولة، وتتبع التدقيق، ومصالحة عملية ربما نجحت عند المزوّد ولم يصل ردها.

### Shipment

`pending → booked → picked_up → in_transit → out_for_delivery → delivered`

والإلغاء مسموح فقط قبل التسليم، مع منع الانتقال العكسي. يجب أن تكون عملية الحجز الخارجي للشحنة مثل الدفع: claim محلي أولًا، ثم provider call خارج transaction، ثم تحديث الحالة أو إدخالها إلى reconciliation.

### Order

حالات الطلب لا تعني حالة الدفع وحدها. يجب فصل:

| بُعد | أمثلة |
|---|---|
| Order lifecycle | `pending`, `confirmed`, `processing`, `completed`, `cancelled`, `refunded` |
| Payment lifecycle | `initiating`, `pending`, `paid`, `failed`, `refunded` |
| Fulfillment lifecycle | `pending`, `booked`, `in_transit`, `delivered`, `cancelled` |

لا تجعل `order.status = confirmed` دليلًا وحيدًا على أن المال وصل. القرار يجب أن يعتمد على payment aggregate أو projection موثقة.

## مصفوفة فشل الـ Checkout

| المرحلة | النتيجة المحلية | الإجراء | واجهة العميل |
|---|---|---|---|
| مفتاح checkout مستخدم لنفس الطلب | إعادة نفس الطلب | لا تنشئ Order جديدًا | `200/201` مع نفس المورد |
| مفتاح checkout مستخدم لطلب آخر | لا تغيير | ارفض لمنع تسريب/خلط البيانات | `409` |
| السلة فارغة | rollback | لا تنشئ طلبًا | `422` |
| منتج غير متاح أو سعر مفقود | rollback | لا تحجز المخزون | `422` |
| مخزون غير كافٍ | rollback | أعد الكمية المحجوزة ضمن transaction | `422` |
| عنوان غير صالح | rollback | لا تنشئ شحنة | `422` |
| طريقة شحن غير فعالة | rollback | لا تنشئ shipment | `422` |
| فشل إنشاء shipment محليًا | rollback | لا تنشئ order نهائيًا | `422/409` حسب السبب |
| فشل بوابة الدفع قبل claim | الطلب موجود والدفع غير موجود | أعد المحاولة بمفتاح جديد أو نفس العملية | `503` أو `422` |
| فشل gateway بعد claim | الطلب موجود وpayment=`failed` | reconciliation أو retry آمن | `503` |
| timeout مع احتمال نجاح المزوّد | payment=`initiating` أو `failed` مع reconciliation | لا تنشئ payment ثانية؛ استعلم أو انتظر webhook | `409/202` |
| Webhook مكرر | لا تغيير | unique provider/event_id | `200` idempotent |
| Webhook غير صالح التوقيع | لا تغيير | سجّل الحادثة دون تغيير payment | `401/400` |
| payment مدفوع مسبقًا | لا تعيد الشحن | أعد الحالة الحالية | `409` عند محاولة transition غير صالح |
| refund مكرر | لا تغيير | أعد الحالة أو ارفض transition | `409` |

## قاعدة idempotency

لكل عملية خارجية مفتاح مستقل ومحدد النطاق:

| العملية | المفتاح |
|---|---|
| إنشاء Order | `checkout_idempotency_key` |
| إنشاء Payment | `payment_idempotency_key` |
| حجز Shipment | `shipment_idempotency_key` |
| Refund | `refund_idempotency_key` |
| Webhook | `(provider, event_id)` |

المفتاح لا يُستخدم لإخفاء اختلاف payload. عند إعادة استخدامه يجب مقارنة `order_id`, `user_id`, `amount`, `currency`, و`method`. أي اختلاف يعيد `409`.

## مصفوفة التفويض

| العملية | Customer | Support | Order Manager | Manager | Owner/Admin |
|---|---:|---:|---:|---:|---:|
| عرض طلباته | نعم | نعم حسب السياسة | نعم | نعم | نعم |
| إنشاء checkout لنفسه | نعم | لا | لا | لا | لا |
| عرض كل الطلبات | لا | نعم | نعم | نعم | نعم |
| تأكيد الطلب | لا | لا | نعم | نعم | نعم |
| إلغاء طلب | طلبه قبل الشحن فقط | لا | نعم وفق السياسة | نعم | نعم |
| إنشاء/تعديل طريقة شحن | لا | لا | لا | حسب الصلاحية | نعم |
| تغيير حالة shipment يدويًا | لا | لا | نعم | نعم | نعم |
| عرض payments | Payments الخاصة به | قراءة دعم | نعم | نعم | نعم |
| confirm payment | لا | لا | لا افتراضيًا | حسب سياسة مالية | نعم |
| refund payment | لا | لا | لا افتراضيًا | حسب سياسة مالية | نعم |
| تغيير gateway credentials | لا | لا | لا | لا | Admin فقط |
| معالجة webhook | ليس endpoint للمستخدم | ليس endpoint للمستخدم | ليس endpoint للمستخدم | ليس endpoint للمستخدم | System signature فقط |

يجب تطبيق التفويض مرتين: في Form Request للطلبات العادية، وفي Use Case أو Policy على المورد نفسه. لا تعتمد على الدور وحده دون فحص ownership وحالة المورد.

## تصميم Adapter لمزوّد فعلي

يجب إنشاء Adapter مستقل لكل مزوّد، مثل:

```text
PaymentGatewayInterface
├── CashOnDeliveryGateway
├── ProviderAGateway
└── ProviderBGateway
```

الـ Adapter مسؤول عن تحويل:

```text
ProviderRequest → ProviderResponse → GatewayResult
```

ولا يحق له إعادة payload الخام إلى Domain. يجب أن يحتوي `GatewayResult` على `status`, `provider_reference`, `provider_event_id`, و`metadata` المسموح بها فقط.

التكامل الفعلي يحتاج تحديد مزوّد واحد، وبيئة sandbox، وعملة الحساب، وسياسة 3-D Secure، وwebhook secret. لا ينبغي اختيار مزوّد أو افتراض API من دون هذا القرار لأنه يغير شكل redirect، capture، refund، وwebhook.

## التشغيل الإنتاجي

يجب تشغيل Queue للـ payment/shipping retries مع backoff، وحد أقصى للمحاولات، وdead-letter أو جدول reconciliation. يجب تسجيل `correlation_id`, `order_id`, `payment_id`, `provider`, و`provider_reference` في كل log. يجب منع تسجيل PAN أو CVV أو secrets.

يجب توفير لوحات مراقبة لـ:

| المؤشر | التنبيه المقترح |
|---|---|
| payments في `initiating` أقدم من حد زمني | تنبيه عالي |
| payments `paid` بلا Order confirmed | تنبيه مالي |
| orders confirmed بلا payment paid | تنبيه مالي |
| shipments booked بلا tracking | تنبيه تشغيلي |
| webhook signature failures | تنبيه أمني |
| idempotency conflicts | تنبيه احتيال/خلل عميل |
| provider timeout/error rate | تنبيه موثوقية |

قبل الإنتاج يجب تطبيق rate limiting على checkout وpayment وwebhook، والتحقق من توقيع webhook قبل parsing، وتدوير الأسرار، وفصل مفاتيح sandbox عن production، وإجراء health checks لا تعتمد على نجاح عملية دفع حقيقية.

## الاختبارات المطلوبة

يجب إضافة اختبارات متوازية أو integration tests تغطي إنشاء طلبين بنفس المفتاح، ومفتاح واحد لطلبين مختلفين، وإعادة الطلب بعد timeout، ووصول webhook مرتين، ووصول webhook قبل رد create، ونجاح gateway ثم فشل تحديث قاعدة البيانات، وrefund المتزامن، ومحاولات الوصول إلى payment أو shipment لمستخدم آخر.

يجب أن تستخدم اختبارات بوابة الدفع Fake Adapter يسجل عدد الاستدعاءات. اختبار idempotency الناجح يجب أن يثبت أن استدعاء المزود حدث مرة واحدة فقط، وليس مجرد أن عدد الصفوف يساوي واحدًا.

## التغييرات المنفذة في هذه الدفعة

تمت إضافة `PaymentInProgressException`، وتحديث `CreatePayment` لمنع إعادة استخدام payment بحالة `initiating`، وتحديث الـ Central Error Handler لإرجاع `409`، وتعديل `Checkout` بحيث تنتهي المعاملة المحلية قبل استدعاء gateway، وإضافة جدول `payment_webhook_events` مع unique deduplication key.

## القيود الحالية

لا توجد في بيئة التنفيذ PHP أو `vendor`، لذلك تعذر تشغيل PHPUnit وLaravel migrations في هذه الجلسة. يجب تشغيل `composer install` ثم `php artisan test` و`php artisan migrate:fresh --seed` في CI أو بيئة التطوير. كما أن Adapter لمزوّد مالي حقيقي لا يمكن إكماله بأمان قبل اختيار المزوّد وتزويد إعدادات sandbox وwebhook.

## References

[1]: https://microservices.io/patterns/data/transactional-outbox.html "Transactional Outbox Pattern"

[2]: https://docs.stripe.com/api/idempotent_requests "Stripe Idempotent Requests"

[3]: https://martinfowler.com/articles/patterns-of-distributed-systems/saga.html "Saga Pattern"
