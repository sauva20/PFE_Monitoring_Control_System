import re
import json

controller_file = "c:/laragon/www/APARB.Braun/app/Http/Controllers/InspeksiController.php"
en_path = "c:/laragon/www/APARB.Braun/lang/en.json"
id_path = "c:/laragon/www/APARB.Braun/lang/id.json"

questions = [
    'Apakah APAR tersebut berada dalam lokasi yang mudah diakses dan terlihat dengan jelas?',
    'Apakah penunjuk tekanan pada APAR menunjukkan tekanan yang sesuai?',
    'Apakah segel keselamatan pada APAR terjaga dan tidak rusak?',
    'Apakah tabung APAR dalam kondisi baik dan tidak terdapat kerusakan fisik?',
    'Apakah spindel pengatur aliran pada APAR berfungsi dengan baik?',
    'Apakah tuas pemadam pada APAR dapat dioperasikan dengan mudah dan bebas dari kebocoran?',
    'Apakah nozzle atau alat semprot pada APAR tidak tersumbat atau rusak?',
    'Apakah selang pemadam pada APAR tidak terdapat kerusakan, sobek, atau kebocoran?',
    'Apakah label instruksi penggunaan APAR masih terpasang dan mudah dibaca?',
    'Apakah tanggal terakhir inspeksi APAR telah dicatat dan sesuai dengan jadwal inspeksi yang ditetapkan?',
    'Apakah APAR tersebut dilengkapi dengan segala perlengkapan tambahan yang diperlukan, seperti penyangga dinding atau bracket pemasangan?',
    'Apakah petunjuk penggunaan APAR dan tanda peringatan bahaya terkait penggunaan APAR tersedia dan mudah diakses?',
    'Apakah petugas yang bertanggung jawab terhadap APAR terlatih dalam penggunaan dan pemeliharaan APAR?',
    'Apakah daerah sekitar APAR bebas dari bahan yang mudah terbakar atau bahan yang dapat menghambat akses ke APAR?',
    'Apakah APAR tersebut telah diuji atau dirakit kembali setelah digunakan sebelumnya?'
]

english_translations = [
    'Is the PFE located in an easily accessible and clearly visible location?',
    'Does the pressure gauge on the PFE indicate the correct pressure?',
    'Is the safety seal on the PFE intact and undamaged?',
    'Is the PFE cylinder in good condition and free from physical damage?',
    'Is the flow control spindle on the PFE functioning properly?',
    'Can the extinguisher lever on the PFE be operated easily and free from leaks?',
    'Is the nozzle or spray device on the PFE not clogged or damaged?',
    'Is the extinguisher hose on the PFE free from damage, tears, or leaks?',
    'Is the PFE instruction label still attached and easy to read?',
    'Has the last inspection date of the PFE been recorded and in accordance with the established inspection schedule?',
    'Is the PFE equipped with all necessary additional equipment, such as wall mounts or mounting brackets?',
    'Are the PFE instructions and danger warning signs related to PFE use available and easily accessible?',
    'Are the personnel responsible for the PFE trained in the use and maintenance of the PFE?',
    'Is the area around the PFE free from flammable materials or materials that could obstruct access to the PFE?',
    'Has the PFE been tested or reassembled after previous use?'
]

# Update InspeksiController
with open(controller_file, "r", encoding="utf-8") as f:
    content = f.read()

for q in questions:
    old = f"'{q}'"
    new = f"__('{q}')"
    content = content.replace(old, new)

with open(controller_file, "w", encoding="utf-8") as f:
    f.write(content)

# Update en.json
with open(en_path, "r", encoding="utf-8") as f:
    en_dict = json.load(f)

for i, q in enumerate(questions):
    en_dict[q] = english_translations[i]

with open(en_path, "w", encoding="utf-8") as f:
    json.dump(en_dict, f, indent=4, ensure_ascii=False)

# Update id.json
with open(id_path, "r", encoding="utf-8") as f:
    id_dict = json.load(f)

for i, q in enumerate(questions):
    if q not in id_dict:
        id_dict[q] = q

with open(id_path, "w", encoding="utf-8") as f:
    json.dump(id_dict, f, indent=4, ensure_ascii=False)

print("Questions translated.")
