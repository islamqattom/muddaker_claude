@extends('layouts.app')
@section('title', 'طرق الحفظ المتقن')
@section('page-title', 'طرق حفظ القرآن الكريم')
@section('role-label', 'طالب')
@section('sidebar-menu')
    @include('student.partials.sidebar')
@endsection
@section('content')
<div class="card mb-4" style="background: linear-gradient(135deg, #1a5276 0%, #0d3349 100%); color: #fff;">
    <div class="card-body p-4 text-center">
        <i class="fas fa-book-open fa-3x mb-3" style="opacity:0.7;"></i>
        <h4 class="mb-2">طريقة الحفظ للوصول إلى الإتقان</h4>
        <p class="mb-0" style="opacity:0.8; font-size:16px;">﴿ وَرَتِّلِ الْقُرْآنَ تَرْتِيلًا ﴾</p>
        <div class="mt-3 p-2 rounded d-inline-block" style="background:rgba(201,162,39,0.3);">
            <i class="fas fa-clock me-2"></i>
            <strong>مدة حفظ الصفحة الواحدة: من 45 دقيقة إلى ساعة</strong>
        </div>
    </div>
</div>
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><h5 class="mb-0"><i class="fas fa-list-ol me-2"></i>خطوات الحفظ المتقن</h5></div>
            <div class="card-body p-0">
                @php
                $steps = [
                    ['num'=>1,'title'=>'تجزئة الصفحة إلى أربعة أرباع','desc'=>'قسّم الصفحة إلى 4 أجزاء متساوية تقريباً','icon'=>'fa-cut','color'=>'#3498db','tip'=>'ضع علامات خفيفة بالقلم لتحديد الأرباع'],
                    ['num'=>2,'title'=>'الاستماع للربع الأول من 3 إلى 5 مرات','desc'=>'استمع للربع الأول بصوت الحصري أو المنشاوي من 3 إلى 5 مرات','icon'=>'fa-headphones','color'=>'#9b59b6','tip'=>'أغمض عينيك أثناء الاستماع لتركيز السمع'],
                    ['num'=>3,'title'=>'قراءة الربع مع المتابعة والتدقيق','desc'=>'اقرأ مع المصحف مع التدقيق على الحركات والتشكيل','icon'=>'fa-eye','color'=>'#27ae60','tip'=>'انتبه لحركات التشكيل الصغيرة والمدود'],
                    ['num'=>4,'title'=>'تكرار الآية الأولى من 7 إلى 10 مرات','desc'=>'كرر الآية الأولى وحدها من سبع إلى عشر مرات','icon'=>'fa-redo','color'=>'#e74c3c','tip'=>'كرر حتى تستطيع قولها دون النظر للمصحف'],
                    ['num'=>5,'title'=>'تكرار الآية الثانية من 7 إلى 10 مرات','desc'=>'انتقل للآية الثانية وكررها وحدها من سبع إلى عشر مرات','icon'=>'fa-redo','color'=>'#e67e22','tip'=>'تأكد من استقرار الآية الثانية قبل الجمع'],
                    ['num'=>6,'title'=>'تكرار الآيتين معاً من 7 إلى 10 مرات','desc'=>'اجمع الآيتين وكررهما للربط والتوصيل','icon'=>'fa-link','color'=>'#f39c12','tip'=>'الجمع يقوي الربط والتذكر'],
                    ['num'=>7,'title'=>'الانتقال للربع الثاني','desc'=>'أعد نفس عملية الحفظ مع الربع الثاني','icon'=>'fa-arrow-down','color'=>'#1abc9c','tip'=>'لا تنتقل حتى تتأكد من إتقان الربع السابق'],
                    ['num'=>8,'title'=>'تكرار الربعين معاً 5 مرات','desc'=>'بعد حفظ الربعين اجمعهما وكررهما خمس مرات','icon'=>'fa-sync','color'=>'#16a085','tip'=>'هذا الجمع يقوي التواصل بين الأجزاء'],
                    ['num'=>9,'title'=>'إكمال بقية الصفحة','desc'=>'كرر نفس العملية مع الربع الثالث والرابع','icon'=>'fa-flag-checkered','color'=>'#2c3e50','tip'=>'اجمع الأرباع تدريجياً: 1+2 ثم 1+2+3 ثم كاملة'],
                    ['num'=>10,'title'=>'تسميع الصفحة لشخص متقن','desc'=>'سمّع الصفحة لأحد الأقارب أو الأصدقاء المتقنين','icon'=>'fa-users','color'=>'#8e44ad','tip'=>'التسميع يكشف نقاط الضعف ويثبت الحفظ'],
                ];
                @endphp
                @foreach($steps as $step)
                <div class="p-4 border-bottom">
                    <div class="d-flex gap-3">
                        <div style="width:45px;height:45px;border-radius:50%;background:{{ $step['color'] }};display:flex;align-items:center;justify-content:center;color:#fff;font-weight:bold;font-size:16px;flex-shrink:0;">
                            {{ $step['num'] }}
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="fas {{ $step['icon'] }}" style="color:{{ $step['color'] }};"></i>
                                <h6 class="mb-0 fw-bold">{{ $step['title'] }}</h6>
                            </div>
                            <p class="mb-1 text-muted" style="font-size:14px;">{{ $step['desc'] }}</p>
                            <div class="mt-1 p-2 rounded" style="background:{{ $step['color'] }}22;border-right:3px solid {{ $step['color'] }};font-size:12px;">
                                <i class="fas fa-lightbulb me-1" style="color:{{ $step['color'] }};"></i>
                                <strong>نصيحة:</strong> {{ $step['tip'] }}
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header"><h6><i class="fas fa-stopwatch me-2"></i>مؤقت الحفظ</h6></div>
            <div class="card-body text-center">
                <div id="timerDisplay" style="font-size:3rem;font-weight:bold;color:#1a5276;font-family:monospace;">00:00:00</div>
                <div class="text-muted mb-3">الهدف: 45-60 دقيقة للصفحة</div>
                <div class="progress mb-3" style="height:10px;">
                    <div class="progress-bar bg-info" id="timerProgress" style="width:0%;transition:width 0.5s;"></div>
                </div>
                <div class="d-flex gap-2 justify-content-center">
                    <button class="btn btn-success" onclick="startTimer()"><i class="fas fa-play me-1"></i>ابدأ</button>
                    <button class="btn btn-warning" onclick="pauseTimer()"><i class="fas fa-pause me-1"></i>إيقاف</button>
                    <button class="btn btn-danger" onclick="resetTimer()"><i class="fas fa-stop me-1"></i>إعادة</button>
                </div>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-header"><h6><i class="fas fa-redo me-2"></i>عداد التكرار</h6></div>
            <div class="card-body text-center">
                <div id="repCount" style="font-size:4rem;font-weight:bold;color:#27ae60;">0</div>
                <div class="text-muted mb-2">الهدف: <span id="repTarget">7</span> مرات</div>
                <div class="progress mb-3" style="height:12px;">
                    <div class="progress-bar bg-success" id="repProgress" style="width:0%;"></div>
                </div>
                <div class="d-flex gap-2 justify-content-center mb-3">
                    <button class="btn btn-success btn-lg px-4" onclick="incrementRep()"><i class="fas fa-plus me-1"></i>+1</button>
                    <button class="btn btn-outline-secondary" onclick="resetRep()"><i class="fas fa-undo"></i></button>
                </div>
                <div class="btn-group w-100">
                    <button class="btn btn-sm btn-outline-primary" onclick="setTarget(7)">7× آية</button>
                    <button class="btn btn-sm btn-outline-primary" onclick="setTarget(10)">10× آية</button>
                    <button class="btn btn-sm btn-outline-success" onclick="setTarget(5)">5× ربعين</button>
                </div>
            </div>
        </div>
        <div class="card" style="background:linear-gradient(135deg,#1a5276,#2980b9);color:#fff;">
            <div class="card-body p-4">
                <h6 class="mb-3"><i class="fas fa-star me-2"></i>نقاط مهمة</h6>
                <ul class="mb-0" style="font-size:13px;line-height:2.2;padding-right:20px;">
                    <li>الاستمرارية يومياً أهم من الكمية</li>
                    <li>الحفظ في الصباح الباكر أثبت</li>
                    <li>التسميع يقوي الذاكرة بشكل كبير</li>
                    <li>لا تنتقل قبل إتقان الجزء الحالي</li>
                    <li>الطهارة والوضوء تساعد على التركيز</li>
                    <li>ابدأ بالاستعاذة والبسملة دائماً</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
let timerInterval=null,seconds=0,repCount=0,repTarget=7;
function startTimer(){if(timerInterval)return;timerInterval=setInterval(()=>{seconds++;updateTimerDisplay();},1000);}
function pauseTimer(){if(timerInterval){clearInterval(timerInterval);timerInterval=null;}}
function resetTimer(){pauseTimer();seconds=0;updateTimerDisplay();document.getElementById('timerProgress').style.width='0%';}
function updateTimerDisplay(){const h=String(Math.floor(seconds/3600)).padStart(2,'0');const m=String(Math.floor((seconds%3600)/60)).padStart(2,'0');const s=String(seconds%60).padStart(2,'0');document.getElementById('timerDisplay').textContent=`${h}:${m}:${s}`;const p=Math.min((seconds/3150)*100,100);document.getElementById('timerProgress').style.width=p+'%';}
function incrementRep(){repCount++;document.getElementById('repCount').textContent=repCount;const p=Math.min((repCount/repTarget)*100,100);document.getElementById('repProgress').style.width=p+'%';if(repCount>=repTarget){toastr.success('أحسنت! أكملت عدد التكرارات المطلوب 🎉');setTimeout(()=>{repCount=0;document.getElementById('repCount').textContent='0';document.getElementById('repProgress').style.width='0%';},2000);}}
function resetRep(){repCount=0;document.getElementById('repCount').textContent='0';document.getElementById('repProgress').style.width='0%';}
function setTarget(t){repTarget=t;document.getElementById('repTarget').textContent=t;resetRep();}
</script>
@endpush
