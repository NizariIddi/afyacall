<?php
/**
 * lang.php — Multi-language engine (Swahili + English)
 *
 * Usage:
 *   $lang = detect_lang($text_array[0] ?? '');   // '1' = Swahili, '2' = English
 *   echo t('welcome', $lang);
 *   echo t('greeting', $lang, ['name' => 'Amina']);  // placeholder support
 *
 * Adding a new string: add a key under both 'sw' and 'en' below.
 */

define('LANG_SW', 'sw');
define('LANG_EN', 'en');

// ── USSD string table ──────────────────────────────────────────────────────
$GLOBALS['_LANG'] = [

    // ── Root menu ────────────────────────────────────────────────────────
    'root_welcome'      => ['sw' => "Karibu AfyaCall\n1. Kiswahili\n2. English",
                            'en' => "Welcome to AfyaCall\n1. Kiswahili\n2. English"],

    // ── Main menu ────────────────────────────────────────────────────────
    'main_menu'         => ['sw' => "Chagua Huduma\n1. Rekodi za Matibabu\n2. Miadi\n3. Vidokezo vya Afya\n4. Taarifa za Dharura",
                            'en' => "Select Service\n1. Medical Records\n2. Appointments\n3. Health Tips\n4. Emergency Alerts"],

    // ── Medical records ──────────────────────────────────────────────────
    'enter_code_med'    => ['sw' => "Weka Namba ya Mgonjwa (mfano: AC-12345)",
                            'en' => "Enter Patient Code (e.g. AC-12345)"],
    'record_found'      => ['sw' => "Mgonjwa: {name}\nTatizo: {diagnosis}\nDawa: {medication}\nMiadi ijayo: {next}",
                            'en' => "Patient: {name}\nDiagnosis: {diagnosis}\nMedication: {medication}\nNext Appt: {next}"],
    'record_not_found'  => ['sw' => "Rekodi haijapatikana kwa namba: {code}",
                            'en' => "Record not found for code: {code}"],

    // ── Appointments ─────────────────────────────────────────────────────
    'enter_code_appt'   => ['sw' => "Weka Namba ya Mgonjwa",
                            'en' => "Enter Patient Code"],
    'appt_found'        => ['sw' => "Mgonjwa: {name}\nTarehe: {date}\nHali: {status}\nMaelezo: {remarks}",
                            'en' => "Patient: {name}\nDate: {date}\nStatus: {status}\nRemarks: {remarks}"],
    'appt_not_found'    => ['sw' => "Miadi haijapatikana",
                            'en' => "Appointment not found"],
    'no_appt'           => ['sw' => "Hakuna miadi iliyoorodheshwa",
                            'en' => "No appointment on record"],

    // ── Health tips ──────────────────────────────────────────────────────
    'tips_menu'         => ['sw' => "Vidokezo vya Afya\n1. Lishe\n2. Mazoezi\n3. Malaria\n4. Kisukari\n5. VVU/UKIMWI",
                            'en' => "Health Tips\n1. Nutrition\n2. Exercise\n3. Malaria\n4. Diabetes\n5. HIV/AIDS"],
    'tip_nutrition'     => ['sw' => "Kula matunda, mboga na nafaka kila siku. Epuka sukari nyingi na mafuta. Kunywa maji safi angalau lita 2 kwa siku.",
                            'en' => "Eat fruits, vegetables and whole grains daily. Avoid excess sugar and fats. Drink at least 2 litres of clean water per day."],
    'tip_exercise'      => ['sw' => "Fanya mazoezi kwa dakika 30 kila siku. Kutembea, kukimbia au kucheza michezo kunaweza kuboresha afya yako.",
                            'en' => "Exercise for 30 minutes daily. Walking, running or playing sports can significantly improve your health and wellbeing."],
    'tip_malaria'       => ['sw' => "Lala kwenye chandarua chenye dawa. Angalia maeneo ya maji yaliyotuama. Tafuta matibabu mapema ukipata dalili za malaria.",
                            'en' => "Sleep under a treated mosquito net. Eliminate stagnant water around your home. Seek early treatment if you notice malaria symptoms."],
    'tip_diabetes'      => ['sw' => "Punguza sukari na wanga. Pima sukari yako mara kwa mara. Dawa zako zinaweza kusaidia kudhibiti ugonjwa wa kisukari.",
                            'en' => "Reduce sugar and carbohydrate intake. Monitor your blood sugar regularly. Your medication can help control diabetes effectively."],
    'tip_hiv'           => ['sw' => "Pima VVU mara kwa mara. Tumia kondomu kila wakati. Watu walio na VVU wanaweza kuishi maisha mazuri kwa ARVs.",
                            'en' => "Get tested for HIV regularly. Always use condoms. People living with HIV can live healthy lives with proper ARV treatment."],

    // ── Emergency alerts ─────────────────────────────────────────────────
    'alert_menu'        => ['sw' => "Taarifa za Dharura\n1. Nambari za dharura\n2. Hospitali zilizo karibu\n3. Ripoti dharura",
                            'en' => "Emergency Alerts\n1. Emergency numbers\n2. Nearby hospitals\n3. Report emergency"],
    'alert_numbers'     => ['sw' => "Nambari za Dharura:\nPolisi: 112\nZima moto: 115\nAfya: 0800 110 084\nMadawa ya kulevya: 116",
                            'en' => "Emergency Numbers:\nPolice: 112\nFire: 115\nHealth Hotline: 0800 110 084\nPoison Control: 116"],
    'alert_hospitals'   => ['sw' => "Hospitali za karibu:\n1. MNH - Dar es Salaam\n2. Muhimbili - Dar\n3. Temeke Hospitali\nPiga simu kwa maelezo zaidi.",
                            'en' => "Nearby Hospitals:\n1. MNH - Dar es Salaam\n2. Muhimbili - Dar\n3. Temeke Hospital\nCall for more information."],
    'alert_report'      => ['sw' => "Ombi lako la dharura limepokelewa. Timu yetu itawasiliana nawe hivi karibuni. Kwa dharura ya papo hapo, piga simu 112.",
                            'en' => "Your emergency report has been received. Our team will contact you shortly. For immediate emergencies, please call 112."],

    // ── Shared ──────────────────────────────────────────────────────────
    'invalid'           => ['sw' => "Chaguo si sahihi. Tafadhali jaribu tena.",
                            'en' => "Invalid option. Please try again."],
    'none'              => ['sw' => "Hakuna", 'en' => "None"],
];

/**
 * Detect language from first USSD input digit.
 * '1' → Swahili (default), '2' → English
 */
function detect_lang(string $first_digit): string {
    return $first_digit === '2' ? LANG_EN : LANG_SW;
}

/**
 * Translate a key, with optional {placeholder} substitution.
 */
function t(string $key, string $lang = LANG_SW, array $vars = []): string {
    $strings = $GLOBALS['_LANG'];
    $str = $strings[$key][$lang] ?? ($strings[$key][LANG_EN] ?? "[$key]");
    foreach ($vars as $k => $v) {
        $str = str_replace('{' . $k . '}', $v, $str);
    }
    return $str;
}
