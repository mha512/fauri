<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Message;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Enforce safety: Demo seed credentials must never run in production
        if (!app()->environment(['local', 'testing'])) {
            $this->command?->warn('Demo database seeder (demo@fauri.pk) is strictly disabled outside local/testing environments.');
            return;
        }

        // 1. Create primary demo user
        $user = User::firstOrCreate(
            ['email' => 'demo@fauri.pk'],
            [
                'name' => 'Ali Raza',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // 2. Create primary demo business
        $business = Business::firstOrCreate(
            ['user_id' => $user->id],
            [
                'name' => 'Gulberg Apparel & Studio',
                'slug' => 'gulberg-apparel',
                'industry' => 'ecommerce',
                'whatsapp_number' => '+92 300 8472910',
                'currency' => 'PKR',
                'settings' => [
                    'auto_triage' => true,
                    'high_urgency_alerts' => true,
                    'language_pref' => 'bilingual',
                ],
            ]
        );

        // 3. Create realistic customers
        $customersData = [
            ['name' => 'Zainab Bibi', 'phone' => '+923001234567', 'email' => 'zainab@example.com', 'notes' => 'VIP customer, frequent buyer'],
            ['name' => 'Hamza Sheikh', 'phone' => '+923219876543', 'email' => 'hamza.s@example.com', 'notes' => 'DHA Phase 5 resident'],
            ['name' => 'Dr. Asim Farooq', 'phone' => '+923334567890', 'email' => 'asim.f@clinic.pk', 'notes' => 'Medical doctor at Mayo Hospital'],
            ['name' => 'Fatima Zahra', 'phone' => '+923451122334', 'email' => 'fatima.z@example.com', 'notes' => 'Bridal order client'],
            ['name' => 'Usman Tariq', 'phone' => '+923129988776', 'email' => 'usman.t@example.com', 'notes' => 'Multan wholesale inquiries'],
            ['name' => 'Sana Mir', 'phone' => '+923015544332', 'email' => 'sana.mir@example.com', 'notes' => 'Fashion influencer'],
            ['name' => 'Bilal Ahmed', 'phone' => '+923224455667', 'email' => 'bilal.a@example.com', 'notes' => 'Repeated returns dispute'],
            ['name' => 'Ayesha Malik', 'phone' => '+923347788990', 'email' => 'ayesha.m@example.com', 'notes' => 'Islamabad delivery'],
            ['name' => 'Khurram Shahzad', 'phone' => '+923158877665', 'email' => 'khurram@example.com', 'notes' => 'Real estate client'],
            ['name' => 'Noreen Akhtar', 'phone' => '+923033322110', 'email' => 'noreen@example.com', 'notes' => 'Online student parent'],
        ];

        $customerModels = [];
        foreach ($customersData as $c) {
            $customerModels[$c['name']] = Customer::firstOrCreate(
                ['business_id' => $business->id, 'phone' => $c['phone']],
                [
                    'name' => $c['name'],
                    'email' => $c['email'],
                    'notes' => $c['notes'],
                ]
            );
        }

        // 4. Create 25 realistic, varied messages (Urgent on top, today & recent dates)
        $now = Carbon::now();
        $todayMorning = Carbon::today()->addHours(10);
        $todayAfternoon = Carbon::today()->addHours(14);
        $yesterday = Carbon::yesterday()->addHours(15);
        $twoDaysAgo = Carbon::now()->subDays(2);

        $demoMessages = [
            // URGENT High Priority (Today)
            [
                'customer' => 'Fatima Zahra',
                'body' => 'URGENT: Bhai please emergency hai kal meri shadi ka event hai aur bridal dress parcel abhi tak receive nahi hua! TCS manager keh raha hai rider out of reach hai. Please call pick karein foran!',
                'source' => 'paste',
                'category' => 'urgent',
                'urgency' => 'high',
                'summary' => 'CRITICAL / URGENT: Wedding function tomorrow, bridal dress parcel delayed in transit',
                'confidence' => 0.9450,
                'status' => 'pending',
                'detected_language' => 'Roman Urdu',
                'created_at' => $now->copy()->subMinutes(12),
            ],
            [
                'customer' => 'Dr. Asim Farooq',
                'body' => 'فوری ایمرجنسی ہے، مریض کو دوائی کا شدید ری ایکشن ہوا ہے اور غلط سیرپ ڈلیور ہو گیا ہے۔ براہ کرم فوراً ڈاکٹر صاحب کو کال کنیکٹ کروائیں۔',
                'source' => 'voice',
                'category' => 'urgent',
                'urgency' => 'high',
                'summary' => 'CRITICAL / URGENT: شدید الرجی ری ایکشن، فوری رابطہ کی درخواست',
                'confidence' => 0.9620,
                'status' => 'in_progress',
                'detected_language' => 'Urdu',
                'created_at' => $now->copy()->subMinutes(35),
            ],
            [
                'customer' => 'Bilal Ahmed',
                'body' => 'bhai police case ban raha hai mere credit card se 12500 extra kat gaye hain order cancel hone k bawajood! Payment foran stop karein warna FIA me complaint kar raha hu.',
                'source' => 'paste',
                'category' => 'complaint',
                'urgency' => 'high',
                'summary' => 'High Dispute Alert: Unauthorized credit card deduction of PKR 12,500',
                'confidence' => 0.8980,
                'status' => 'pending',
                'detected_language' => 'Roman Urdu',
                'created_at' => $now->copy()->subHours(1),
            ],

            // ORDERS (Today & Yesterday)
            [
                'customer' => 'Hamza Sheikh',
                'body' => 'AOA bhai, mujhe ye black linen suit medium size me order confirm karna hai. Cash on Delivery delivery address: House 44 Street 12 Phase 5 DHA Lahore. Please invoice send kr dain.',
                'source' => 'whatsapp_export',
                'category' => 'order',
                'urgency' => 'medium',
                'summary' => 'New Order Request: Black linen suit (Medium) - DHA Phase 5 Lahore COD',
                'confidence' => 0.9230,
                'status' => 'pending',
                'detected_language' => 'Roman Urdu',
                'created_at' => $now->copy()->subHours(2),
            ],
            [
                'customer' => 'Zainab Bibi',
                'body' => 'I want to place an order for 2 silk embroidered shawls in emerald green and maroon. Payment transferred via Nayapay (PKR 8,500), receipt attached.',
                'source' => 'paste',
                'category' => 'order',
                'urgency' => 'low',
                'summary' => 'New Order Request: 2 silk shawls, PKR 8,500 prepaid via Nayapay',
                'confidence' => 0.9510,
                'status' => 'in_progress',
                'detected_language' => 'English',
                'created_at' => $todayMorning->copy()->subHours(1),
            ],
            [
                'customer' => 'Usman Tariq',
                'body' => 'مجھے یہ تین جوڑے ان سلے ہوئے آرڈر کرنے ہیں، کیا ملتان میں کیش آن ڈلیوری کی سہولت موجود ہے؟ کل رقم کتنی بنے گی؟',
                'source' => 'whatsapp_export',
                'category' => 'order',
                'urgency' => 'low',
                'summary' => 'New Order Request: تین ان سلے جوڑے ملتان کیش آن ڈلیوری',
                'confidence' => 0.9140,
                'status' => 'pending',
                'detected_language' => 'Urdu',
                'created_at' => $todayMorning,
            ],
            [
                'customer' => 'Ayesha Malik',
                'body' => 'bhai parcel book kr lein: 1 Kurti large, 1 Trouser medium, address F-8/2 Islamabad. Kal tak lazmi deliver krwa dena please.',
                'source' => 'paste',
                'category' => 'order',
                'urgency' => 'medium',
                'summary' => 'New Order Request: 1 Kurti L, 1 Trouser M - F-8/2 Islamabad',
                'confidence' => 0.8850,
                'status' => 'resolved',
                'detected_language' => 'Roman Urdu',
                'created_at' => $yesterday,
            ],

            // COMPLAINTS
            [
                'customer' => 'Sana Mir',
                'body' => 'bhai ap ne bilkul wrong colour bhej dia hai! Maine royal blue mangwaya tha aap ne dark brown bhej dia hai. Aur stitching bhi threads nikle hue hain.',
                'source' => 'whatsapp_export',
                'category' => 'complaint',
                'urgency' => 'medium',
                'summary' => 'Customer Complaint: Wrong color delivered (Brown instead of Blue) with stitching defects',
                'confidence' => 0.8790,
                'status' => 'in_progress',
                'detected_language' => 'Roman Urdu',
                'created_at' => $todayAfternoon,
            ],
            [
                'customer' => 'Khurram Shahzad',
                'body' => 'Your delivery rider was extremely insolent and demanded an extra 300 PKR claiming petrol charges. The parcel seal was also partially torn upon arrival.',
                'source' => 'paste',
                'category' => 'complaint',
                'urgency' => 'high',
                'summary' => 'Customer Complaint: Rider extortion and torn parcel packaging',
                'confidence' => 0.8650,
                'status' => 'pending',
                'detected_language' => 'English',
                'created_at' => $now->copy()->subHours(3),
            ],
            [
                'customer' => 'Noreen Akhtar',
                'body' => 'کپڑے کا رنگ پہلے ہی دھلائی میں اتر گیا ہے اور استر کا کپڑا بالکل سکڑ گیا ہے۔ اتنی غیر معیاری پروڈکٹ کی امید نہیں تھی آپ سے۔',
                'source' => 'voice',
                'category' => 'complaint',
                'urgency' => 'medium',
                'summary' => 'Customer Complaint: کپڑے کا رنگ خراب اور کپڑا سکڑنے کی شکایت',
                'confidence' => 0.8930,
                'status' => 'pending',
                'detected_language' => 'Urdu',
                'created_at' => $yesterday,
            ],

            // REFUNDS
            [
                'customer' => 'Bilal Ahmed',
                'body' => 'item wapas courier se deliver ho chuki hai aap k warehouse me tracking Leopard #8472910. Meri payment 4200 PKR JazzCash 03224455667 pe reverse karein.',
                'source' => 'paste',
                'category' => 'refund',
                'urgency' => 'medium',
                'summary' => 'Refund/Return Request (PKR 4200): Return delivered via Leopard #8472910, JazzCash payout requested',
                'confidence' => 0.9320,
                'status' => 'pending',
                'detected_language' => 'Roman Urdu',
                'created_at' => $now->copy()->subHours(4),
            ],
            [
                'customer' => 'Zainab Bibi',
                'body' => 'Please process the refund for cancelled order #4491 to my Meezan Bank account. IBAN PK45MEZN000123456789. It has been 5 business days.',
                'source' => 'paste',
                'category' => 'refund',
                'urgency' => 'medium',
                'summary' => 'Refund/Return Request: Cancelled order #4491 refund to Meezan Bank pending 5 days',
                'confidence' => 0.9120,
                'status' => 'in_progress',
                'detected_language' => 'English',
                'created_at' => $yesterday,
            ],
            [
                'customer' => 'Fatima Zahra',
                'body' => 'آرڈر کینسل کر دیا تھا کیونکہ سامان وقت پر نہیں ملا، ادا شدہ پیشگی رقم چھ ہزار روپے ایزی پیسہ اکاؤنٹ پر واپس کی جائے۔',
                'source' => 'whatsapp_export',
                'category' => 'refund',
                'urgency' => 'medium',
                'summary' => 'Refund/Return Request (PKR 6000): پیشگی رقم کی واپسی کا تقاضا',
                'confidence' => 0.9410,
                'status' => 'resolved',
                'detected_language' => 'Urdu',
                'created_at' => $twoDaysAgo,
            ],

            // INQUIRIES
            [
                'customer' => 'Hamza Sheikh',
                'body' => 'AOA bhai, is suit ka fabric pure lawn hai ya blend hai? Aur wash and wear me koi wrinkle-free collection hai Eid k liye?',
                'source' => 'whatsapp_export',
                'category' => 'inquiry',
                'urgency' => 'low',
                'summary' => 'Product/Service Inquiry: Fabric composition and wrinkle-free Eid collection availability',
                'confidence' => 0.9200,
                'status' => 'resolved',
                'detected_language' => 'Roman Urdu',
                'created_at' => $yesterday,
            ],
            [
                'customer' => 'Sana Mir',
                'body' => 'Do you ship to Dubai via DHL or Skynet? If yes, what are the shipping rates for 3 kg parcel and estimated transit time?',
                'source' => 'paste',
                'category' => 'inquiry',
                'urgency' => 'low',
                'summary' => 'Product/Service Inquiry: International shipping to Dubai rates and timelines',
                'confidence' => 0.9380,
                'status' => 'pending',
                'detected_language' => 'English',
                'created_at' => $todayAfternoon,
            ],
            [
                'customer' => 'Dr. Asim Farooq',
                'body' => 'کیا عید سے قبل کسٹمائزڈ سائزنگ کی سلائی ممکن ہو سکے گی اگر آج ناپ بھیج دیا جائے؟',
                'source' => 'whatsapp_export',
                'category' => 'inquiry',
                'urgency' => 'low',
                'summary' => 'Product/Service Inquiry: کسٹمائزڈ سلائی کے اوقات اور عید شیڈول',
                'confidence' => 0.8990,
                'status' => 'resolved',
                'detected_language' => 'Urdu',
                'created_at' => $twoDaysAgo,
            ],
            [
                'customer' => 'Khurram Shahzad',
                'body' => 'bhai DHA Phase 6 me plot visit k liye timing kya hai weekend pe? Aur token money kitne percentage hoti hai?',
                'source' => 'paste',
                'category' => 'inquiry',
                'urgency' => 'low',
                'summary' => 'Product/Service Inquiry: Property site visit timings and token money ratio',
                'confidence' => 0.8750,
                'status' => 'resolved',
                'detected_language' => 'Roman Urdu',
                'created_at' => $twoDaysAgo,
            ],

            // OTHER (Greetings, compliments, feedback)
            [
                'customer' => 'Ayesha Malik',
                'body' => 'JazakAllah khair Ali bhai, parcel receive ho gaya hai. Kapra aur stitching dono boht shaandar hain! 5 star review share kar dia hai.',
                'source' => 'whatsapp_export',
                'category' => 'other',
                'urgency' => 'low',
                'summary' => 'General Message: Customer appreciation and 5-star product feedback',
                'confidence' => 0.9650,
                'status' => 'resolved',
                'detected_language' => 'Roman Urdu',
                'created_at' => $twoDaysAgo,
            ],
            [
                'customer' => 'Noreen Akhtar',
                'body' => 'السلام علیکم، جمعہ مبارک ہو آپ سب کو۔ اللہ تعالیٰ آپ کے کاروبار میں خیر و برکت عطا فرمائے۔',
                'source' => 'whatsapp_export',
                'category' => 'other',
                'urgency' => 'low',
                'summary' => 'General Message: جمعہ مبارک کی دعائیہ مبارکباد',
                'confidence' => 0.9820,
                'status' => 'resolved',
                'detected_language' => 'Urdu',
                'created_at' => $twoDaysAgo,
            ],
            [
                'customer' => 'Usman Tariq',
                'body' => 'Thank you for the prompt resolution on my exchange request earlier this week. Much appreciated!',
                'source' => 'paste',
                'category' => 'other',
                'urgency' => 'low',
                'summary' => 'General Message: Customer thank you note for smooth exchange',
                'confidence' => 0.9540,
                'status' => 'resolved',
                'detected_language' => 'English',
                'created_at' => $yesterday,
            ],
        ];

        foreach ($demoMessages as $dm) {
            $customer = $customerModels[$dm['customer']] ?? null;

            Message::create([
                'business_id' => $business->id,
                'customer_id' => $customer?->id,
                'body' => $dm['body'],
                'source' => $dm['source'],
                'category' => $dm['category'],
                'urgency' => $dm['urgency'],
                'summary' => $dm['summary'],
                'confidence' => $dm['confidence'],
                'status' => $dm['status'],
                'detected_language' => $dm['detected_language'],
                'ai_status' => 'completed',
                'message_timestamp' => $dm['created_at'],
                'created_at' => $dm['created_at'],
                'updated_at' => $dm['created_at'],
            ]);
        }

        $this->command->info("Seeded primary demo user (demo@fauri.pk / password), business '{$business->name}', 10 customers, and 20 categorized messages.");
    }
}
