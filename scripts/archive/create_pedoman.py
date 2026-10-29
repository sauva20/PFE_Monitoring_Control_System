import json

points_id = [
    {"title": "Ketersediaan", "desc": "Pastikan APAR tersedia di tempat yang mudah diakses dan terlihat dengan jelas."},
    {"title": "Tanggal inspeksi terakhir", "desc": "Periksa apakah APAR telah diperiksa dan dikalibrasi sesuai dengan jadwal yang ditetapkan."},
    {"title": "Label dan tanda peringatan", "desc": "Pastikan label dan tanda peringatan pada APAR masih terbaca dengan jelas dan tidak rusak."},
    {"title": "Segel keselamatan", "desc": "Periksa segel keselamatan pada APAR. Pastikan segel tidak rusak atau telah terputus."},
    {"title": "Kondisi fisik", "desc": "Periksa apakah APAR dalam kondisi fisik yang baik, tanpa kerusakan atau kebocoran yang signifikan."},
    {"title": "Penunjuk tekanan", "desc": "Periksa penunjuk tekanan pada APAR. Pastikan tekanan berada dalam rentang yang diperbolehkan."},
    {"title": "Instruksi penggunaan", "desc": "Periksa apakah instruksi penggunaan APAR masih terpasang dan mudah diakses."},
    {"title": "Pemeriksaan visual", "desc": "Lakukan pemeriksaan visual terhadap selang, nozzle, dan katup pemadam. Pastikan tidak ada kerusakan, kebocoran, atau penyumbatan yang signifikan."},
    {"title": "Pengoperasian", "desc": "Pastikan tuas atau pengatur aliran APAR berfungsi dengan baik dan tidak mengalami kebocoran."},
    {"title": "Ketersediaan pelatihan", "desc": "Periksa apakah pengguna APAR telah menjalani pelatihan yang diperlukan."},
    {"title": "Dokumentasi", "desc": "Pastikan catatan inspeksi APAR diisi dengan lengkap dan diperbarui secara teratur."},
    {"title": "Tindakan perbaikan", "desc": "Jika ditemukan masalah atau kekurangan selama inspeksi, pastikan tindakan perbaikan yang tepat diambil dan dicatat."}
]

points_en = [
    {"title": "Availability", "desc": "Ensure the PFE is available in an easily accessible and clearly visible place."},
    {"title": "Last inspection date", "desc": "Check if the PFE has been inspected and calibrated according to the set schedule."},
    {"title": "Labels and warning signs", "desc": "Ensure the labels and warning signs on the PFE are still clearly readable and undamaged."},
    {"title": "Safety seal", "desc": "Check the safety seal on the PFE. Ensure the seal is not damaged or broken."},
    {"title": "Physical condition", "desc": "Check if the PFE is in good physical condition, without significant damage or leaks."},
    {"title": "Pressure gauge", "desc": "Check the pressure gauge on the PFE. Ensure the pressure is within the allowed range."},
    {"title": "Operating instructions", "desc": "Check if the PFE operating instructions are still attached and easily accessible."},
    {"title": "Visual inspection", "desc": "Perform a visual inspection of the hose, nozzle, and extinguisher valve. Ensure there is no significant damage, leak, or blockage."},
    {"title": "Operation", "desc": "Ensure the PFE lever or flow control functions well and does not have leaks."},
    {"title": "Training availability", "desc": "Check if PFE users have undergone the necessary training."},
    {"title": "Documentation", "desc": "Ensure PFE inspection records are filled out completely and updated regularly."},
    {"title": "Corrective action", "desc": "If problems or deficiencies are found during inspection, ensure appropriate corrective action is taken and recorded."}
]

blade_content = """<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ __('Inspection Guidelines') }} - PFE Monitoring Control System</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @font-face {
            font-family: 'Rotis Sans Serif';
            src: url('/fonts/RotisSansSerif.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }

        @font-face {
            font-family: 'Rotis Sans Serif';
            src: url('/fonts/RotisSansSerif-Bold.ttf') format('truetype');
            font-weight: bold;
            font-style: normal;
            font-display: swap;
        }

        body {
            font-family: 'Rotis Sans Serif', sans-serif;
            background-color: #F0F0F0;
            color: #1A1A1A;
            -webkit-tap-highlight-color: transparent;
        }
    </style>
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body class="bg-[#F0F0F0] text-[#1A1A1A] relative min-h-screen w-screen overflow-x-hidden flex flex-col pb-24">

    <!-- Decorative Background Elements -->
    <div class="fixed top-[-10%] left-[-10%] w-[60%] h-[40%] bg-[#009B77] rounded-full mix-blend-multiply filter blur-[100px] opacity-20 pointer-events-none"></div>
    <div class="fixed bottom-10 right-[-10%] w-[50%] h-[40%] bg-[#8A4B9F] rounded-full mix-blend-multiply filter blur-[100px] opacity-15 pointer-events-none"></div>

    <!-- Language Switcher -->
    <div class="fixed top-4 right-4 z-[100] flex items-center gap-1.5">
        <a href="{{ route('set-locale', 'id') }}" class="text-[10px] font-bold px-2 py-1.5 rounded-lg {{ app()->getLocale() == 'id' ? 'bg-[#009B77] text-white border-transparent' : 'bg-white/80 text-slate-500 hover:bg-white border-slate-200/50' }} shadow-sm backdrop-blur-sm border transition-colors">ID</a>
        <a href="{{ route('set-locale', 'en') }}" class="text-[10px] font-bold px-2 py-1.5 rounded-lg {{ app()->getLocale() == 'en' ? 'bg-[#009B77] text-white border-transparent' : 'bg-white/80 text-slate-500 hover:bg-white border-slate-200/50' }} shadow-sm backdrop-blur-sm border transition-colors">EN</a>
    </div>

    <div class="w-full max-w-xl mx-auto relative z-10 flex flex-col p-4 sm:p-6 space-y-6">
        
        <!-- Header Area -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <a href="{{ route('scan.apar', $apar->kode) }}" class="w-8 h-8 rounded-lg bg-white border border-slate-200/60 shadow-sm flex items-center justify-center text-slate-500 hover:text-[#009B77] hover:border-[#009B77] transition-all" title="{{ __('Back') }}">
                    <i class="ph-bold ph-arrow-left text-lg"></i>
                </a>
                <div>
                    <h2 class="text-base font-extrabold text-slate-800 leading-tight">{{ __('Inspection Guidelines') }}</h2>
                    <p class="text-[10px] font-semibold text-slate-500 mt-0.5">{{ __('Points to check during PFE inspection') }}</p>
                </div>
            </div>
        </div>

        <!-- Guidelines List -->
        <div class="bg-white rounded-[24px] shadow-[0_10px_30px_-15px_rgba(0,155,119,0.1)] border border-slate-100 p-5 sm:p-6">
            <div class="space-y-4">
"""

for i, p in enumerate(points_id):
    blade_content += f"""
                <div class="flex gap-3">
                    <div class="w-6 h-6 rounded-full bg-[#009B77]/10 text-[#009B77] flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">
                        {i+1}
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-slate-800 mb-0.5">{{{{ __('{p['title']}') }}}}</h4>
                        <p class="text-[11px] text-slate-500 leading-relaxed">{{{{ __('{p['desc']}') }}}}</p>
                    </div>
                </div>
"""

blade_content += """
            </div>
        </div>
    </div>

    <!-- Floating Action Button -->
    <div class="fixed bottom-0 left-0 right-0 p-4 sm:p-6 bg-gradient-to-t from-[#F0F0F0] via-[#F0F0F0]/90 to-transparent z-40 pb-6">
        <div class="w-full max-w-xl mx-auto">
            <a href="{{ route('inspeksi.mulai', $apar->id) }}" class="w-full bg-[#009B77] hover:bg-[#008264] text-white font-bold py-4 rounded-xl shadow-lg shadow-[#009B77]/30 transition-all text-sm flex items-center justify-center gap-2 tracking-wide">
                <span>{{ __('Start Inspection') }}</span>
                <i class="ph-bold ph-arrow-right"></i>
            </a>
        </div>
    </div>

</body>
</html>
"""

with open("c:/laragon/www/APARB.Braun/resources/views/inspeksi/pedoman.blade.php", "w", encoding="utf-8") as f:
    f.write(blade_content)

# Update translations
with open("c:/laragon/www/APARB.Braun/lang/id.json", "r", encoding="utf-8") as f:
    id_lang = json.load(f)

with open("c:/laragon/www/APARB.Braun/lang/en.json", "r", encoding="utf-8") as f:
    en_lang = json.load(f)

id_lang["Inspection Guidelines"] = "Pedoman Inspeksi"
id_lang["Points to check during PFE inspection"] = "Poin yang perlu diperiksa saat inspeksi APAR"
id_lang["Start Inspection"] = "Mulai Inspeksi"

en_lang["Inspection Guidelines"] = "Inspection Guidelines"
en_lang["Points to check during PFE inspection"] = "Points to check during PFE inspection"
en_lang["Start Inspection"] = "Start Inspection"

for i in range(12):
    id_lang[points_id[i]['title']] = points_id[i]['title']
    id_lang[points_id[i]['desc']] = points_id[i]['desc']
    en_lang[points_id[i]['title']] = points_en[i]['title']
    en_lang[points_id[i]['desc']] = points_en[i]['desc']

with open("c:/laragon/www/APARB.Braun/lang/id.json", "w", encoding="utf-8") as f:
    json.dump(id_lang, f, indent=4, ensure_ascii=False)

with open("c:/laragon/www/APARB.Braun/lang/en.json", "w", encoding="utf-8") as f:
    json.dump(en_lang, f, indent=4, ensure_ascii=False)

print("Created pedoman.blade.php and updated translations.")
