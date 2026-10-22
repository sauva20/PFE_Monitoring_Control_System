<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Verifikasi PIN Viewer - PFE Monitoring Control System</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}?v=3" type="image/svg+xml">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Rotis Sans Serif font -->
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
            font-family: 'Rotis Sans Serif', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #F8FAFC;
        }
        [x-cloak] { display: none !important; }
        
        /* Hide spin buttons on number inputs */
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        input[type=number] {
            -moz-appearance: textfield;
        }
    </style>

    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <!-- AlpineJS -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="min-h-screen w-screen flex flex-col justify-between relative overflow-x-hidden text-slate-800 bg-[#F8FAFC]">

    <!-- Decorative Blurs -->
    <div class="fixed top-[-10%] left-[-10%] w-[50vw] h-[50vw] max-w-[450px] max-h-[450px] bg-[#009B77] rounded-full mix-blend-multiply filter blur-[120px] opacity-15 pointer-events-none"></div>
    <div class="fixed bottom-[-10%] right-[-10%] w-[50vw] h-[50vw] max-w-[450px] max-h-[450px] bg-emerald-600 rounded-full mix-blend-multiply filter blur-[120px] opacity-10 pointer-events-none"></div>

    <!-- Header Navigation -->
    <header class="w-full max-w-5xl mx-auto px-6 py-6 flex items-center justify-between relative z-10">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-[#009B77]/10 border border-[#009B77]/20 flex items-center justify-center text-[#009B77] shadow-sm">
                <i class="ph-duotone ph-fire-extinguisher text-2xl"></i>
            </div>
            <div>
                <h1 class="text-sm sm:text-base font-bold text-[#009B77] leading-tight tracking-wide">PFE Monitoring Control System</h1>
                <p class="text-[10px] sm:text-[11px] font-semibold text-slate-500 tracking-wider">Environment, Health, Safety & Security</p>
            </div>
        </div>

        <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white border border-slate-200 shadow-sm text-xs text-slate-600 font-semibold">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <span>EHSS Public Display Portal</span>
        </div>
    </header>

    <!-- Main Container -->
    <main class="flex-1 flex items-center justify-center p-4 sm:p-6 relative z-10 my-4"
          x-data="{
              digits: ['', '', '', ''],
              showPin: false,
              loading: false,
              get pin() {
                  return this.digits.join('');
              },
              init() {
                  this.$nextTick(() => {
                      const first = this.getBox(0);
                      if (first) {
                          first.focus();
                      }
                  });
              },
              getBox(idx) {
                  return document.getElementById('pin-box-' + idx);
              },
              onInput(index, event) {
                  const raw = event.target.value.replace(/[^0-9]/g, '');
                  if (raw.length > 0) {
                      const char = raw.slice(-1);
                      this.digits[index] = char;
                      event.target.value = char;

                      // Auto-advance focus to next box
                      if (index < 3) {
                          this.$nextTick(() => {
                              const next = this.getBox(index + 1);
                              if (next) {
                                  next.focus();
                                  next.select();
                              }
                          });
                      } else if (this.pin.length === 4) {
                          // Auto-submit when all 4 digits are entered
                          this.$nextTick(() => {
                              this.submitForm();
                          });
                      }
                  } else {
                      this.digits[index] = '';
                      event.target.value = '';
                  }
              },
              onKeydown(index, event) {
                  if (event.key === 'Backspace') {
                      if (!this.digits[index] && index > 0) {
                          event.preventDefault();
                          this.digits[index - 1] = '';
                          const prev = this.getBox(index - 1);
                          if (prev) {
                              prev.value = '';
                              prev.focus();
                          }
                      } else {
                          this.digits[index] = '';
                          event.target.value = '';
                      }
                  } else if (event.key === 'ArrowLeft' && index > 0) {
                      event.preventDefault();
                      const prev = this.getBox(index - 1);
                      if (prev) {
                          prev.focus();
                          prev.select();
                      }
                  } else if (event.key === 'ArrowRight' && index < 3) {
                      event.preventDefault();
                      const next = this.getBox(index + 1);
                      if (next) {
                          next.focus();
                          next.select();
                      }
                  } else if (event.key >= '0' && event.key <= '9') {
                      if (this.digits[index]) {
                          this.digits[index] = event.key;
                          event.target.value = event.key;
                          if (index < 3) {
                              event.preventDefault();
                              this.$nextTick(() => {
                                  const next = this.getBox(index + 1);
                                  if (next) {
                                      next.focus();
                                      next.select();
                                  }
                              });
                          }
                      }
                  }
              },
              handlePaste(event) {
                  event.preventDefault();
                  const text = (event.clipboardData || window.clipboardData).getData('text') || '';
                  const clean = text.replace(/[^0-9]/g, '').slice(0, 4);
                  if (clean.length > 0) {
                      for (let i = 0; i < 4; i++) {
                          this.digits[i] = clean[i] || '';
                          const box = this.getBox(i);
                          if (box) {
                              box.value = this.digits[i];
                          }
                      }
                      if (clean.length === 4) {
                          this.$nextTick(() => {
                              this.submitForm();
                          });
                      } else {
                          const nextIdx = Math.min(clean.length, 3);
                          const next = this.getBox(nextIdx);
                          if (next) {
                              next.focus();
                          }
                      }
                  }
              },
              submitForm() {
                  if (this.pin.length === 4 && !this.loading) {
                      this.loading = true;
                      this.$refs.pinForm.submit();
                  }
              }
          }">
        
        <div class="w-full max-w-md bg-white rounded-3xl shadow-[0_20px_50px_-15px_rgba(0,0,0,0.08)] border border-slate-200/80 overflow-hidden">
            
            <!-- Top Branding Area -->
            <div class="bg-gradient-to-br from-[#009B77] to-[#008264] px-6 py-7 text-white text-center relative overflow-hidden">
                <div class="w-12 h-12 rounded-2xl bg-white/15 backdrop-blur-md border border-white/20 mx-auto flex items-center justify-center text-white mb-3 shadow-inner">
                    <i class="ph-bold ph-shield-check text-2xl"></i>
                </div>
                
                <h2 class="text-lg sm:text-xl font-bold tracking-tight">Verifikasi Akses Viewer</h2>
                <p class="text-xs text-emerald-100 font-medium mt-1">Masukkan 4 digit PIN yang tertera pada email Anda</p>

                <!-- Viewer Identity Pill -->
                <div class="mt-4 pt-3 border-t border-white/15 flex flex-col items-center">
                    <span class="text-[10px] font-bold text-emerald-100 uppercase tracking-wider">Viewer Terdaftar</span>
                    <span class="text-sm font-bold text-white mt-0.5">{{ $guest->name }}</span>
                    <span class="text-xs text-emerald-200 font-semibold mt-0.5">{{ $guest->institution }}</span>
                </div>
            </div>

            <!-- Body Area -->
            <div class="p-6 sm:p-8 space-y-6">

                <!-- Validity Info Bar -->
                <div class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 flex items-center justify-between text-xs">
                    <span class="font-bold text-slate-500 uppercase tracking-wider text-[11px]">Masa Berlaku</span>
                    <span class="font-bold text-slate-700">
                        s.d. {{ \Carbon\Carbon::parse($guest->valid_until)->translatedFormat('d F Y') }} <span class="text-[10px] text-slate-400 font-normal">(23:59 WIB)</span>
                    </span>
                </div>

                @if($isBlocked ?? false)
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-3.5 flex items-start gap-2.5 shadow-sm text-left">
                    <i class="ph-bold ph-lock text-amber-600 text-lg flex-shrink-0 mt-0.5 leading-none"></i>
                    <div>
                        <strong class="text-xs text-amber-900 font-bold block mb-0.5">Perangkat Diblokir</strong>
                        <span class="text-xs text-amber-800 font-medium leading-relaxed block">
                            Akses verifikasi PIN diblokir karena telah mencapai batas maksimal percobaan salah dari perangkat ini.
                        </span>
                    </div>
                </div>
                @endif

                @if($errors->has('pin') && !($isBlocked ?? false))
                <div class="bg-rose-50 border border-rose-200 rounded-xl px-4 py-3 flex items-center gap-2.5 shadow-sm">
                    <i class="ph-bold ph-warning-circle text-rose-600 text-lg flex-shrink-0 leading-none"></i>
                    <span class="text-xs text-rose-800 font-semibold leading-normal">
                        {{ $errors->first('pin') }}
                    </span>
                </div>
                @endif

                @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 flex items-center gap-2.5 shadow-sm">
                    <i class="ph-bold ph-check-circle text-emerald-600 text-lg flex-shrink-0 leading-none"></i>
                    <span class="text-xs text-emerald-800 font-semibold leading-normal">
                        {{ session('success') }}
                    </span>
                </div>
                @endif

                <!-- Form -->
                <form x-ref="pinForm" action="{{ route('display.access.verify', $guest->access_token) }}" method="POST" @submit="loading = true">
                    @csrf
                    
                    <!-- Hidden concatenated PIN -->
                    <input type="hidden" name="pin" :value="pin">

                    <div>
                        <div class="flex items-center justify-between mb-3 px-1">
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider">
                                PIN Akses Viewer (4 Digit)
                            </label>
                            <button type="button" @click="showPin = !showPin" class="text-xs font-semibold text-[#009B77] hover:underline flex items-center gap-1 focus:outline-none">
                                <i class="ph-bold" :class="showPin ? 'ph-eye-slash' : 'ph-eye'"></i>
                                <span x-text="showPin ? 'Sembunyikan' : 'Lihat PIN'"></span>
                            </button>
                        </div>

                        <!-- 4 Segmented Precision Boxes -->
                        <div class="flex items-center justify-center gap-3 sm:gap-4 my-2" @paste="handlePaste($event)">
                            <input :type="showPin ? 'text' : 'password'"
                                   id="pin-box-0"
                                   maxlength="1"
                                   inputmode="numeric"
                                   pattern="[0-9]"
                                   autocomplete="one-time-code"
                                   {{ ($isBlocked ?? false) ? 'disabled' : 'autofocus' }}
                                   @input="onInput(0, $event)"
                                   @keydown="onKeydown(0, $event)"
                                   @focus="$event.target.select()"
                                   class="w-14 h-16 sm:w-16 sm:h-20 text-center font-mono font-bold text-2xl sm:text-3xl text-slate-800 bg-slate-50 border-2 border-slate-200 rounded-2xl focus:bg-white focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none shadow-sm cursor-text disabled:opacity-50 disabled:bg-slate-100 disabled:cursor-not-allowed">

                            <input :type="showPin ? 'text' : 'password'"
                                   id="pin-box-1"
                                   maxlength="1"
                                   inputmode="numeric"
                                   pattern="[0-9]"
                                   autocomplete="one-time-code"
                                   {{ ($isBlocked ?? false) ? 'disabled' : '' }}
                                   @input="onInput(1, $event)"
                                   @keydown="onKeydown(1, $event)"
                                   @focus="$event.target.select()"
                                   class="w-14 h-16 sm:w-16 sm:h-20 text-center font-mono font-bold text-2xl sm:text-3xl text-slate-800 bg-slate-50 border-2 border-slate-200 rounded-2xl focus:bg-white focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none shadow-sm cursor-text disabled:opacity-50 disabled:bg-slate-100 disabled:cursor-not-allowed">

                            <input :type="showPin ? 'text' : 'password'"
                                   id="pin-box-2"
                                   maxlength="1"
                                   inputmode="numeric"
                                   pattern="[0-9]"
                                   autocomplete="one-time-code"
                                   {{ ($isBlocked ?? false) ? 'disabled' : '' }}
                                   @input="onInput(2, $event)"
                                   @keydown="onKeydown(2, $event)"
                                   @focus="$event.target.select()"
                                   class="w-14 h-16 sm:w-16 sm:h-20 text-center font-mono font-bold text-2xl sm:text-3xl text-slate-800 bg-slate-50 border-2 border-slate-200 rounded-2xl focus:bg-white focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none shadow-sm cursor-text disabled:opacity-50 disabled:bg-slate-100 disabled:cursor-not-allowed">

                            <input :type="showPin ? 'text' : 'password'"
                                   id="pin-box-3"
                                   maxlength="1"
                                   inputmode="numeric"
                                   pattern="[0-9]"
                                   autocomplete="one-time-code"
                                   {{ ($isBlocked ?? false) ? 'disabled' : '' }}
                                   @input="onInput(3, $event)"
                                   @keydown="onKeydown(3, $event)"
                                   @focus="$event.target.select()"
                                   class="w-14 h-16 sm:w-16 sm:h-20 text-center font-mono font-bold text-2xl sm:text-3xl text-slate-800 bg-slate-50 border-2 border-slate-200 rounded-2xl focus:bg-white focus:border-[#009B77] focus:ring-4 focus:ring-[#009B77]/15 transition-all outline-none shadow-sm cursor-text disabled:opacity-50 disabled:bg-slate-100 disabled:cursor-not-allowed">
                        </div>

                        <p class="text-[11px] text-slate-400 text-center mt-3 font-medium">
                            Ketik 4 digit angka PIN atau tempel (paste) langsung dari email.
                        </p>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-6">
                        <button type="submit" 
                                :disabled="{{ ($isBlocked ?? false) ? 'true' : 'pin.length !== 4 || loading' }}" 
                                class="w-full bg-[#009B77] hover:bg-[#008264] disabled:bg-slate-200 disabled:text-slate-400 disabled:cursor-not-allowed text-white font-bold py-3.5 px-6 rounded-2xl shadow-[0_6px_20px_rgba(0,155,119,0.25)] disabled:shadow-none transition-all flex items-center justify-center gap-2 text-sm uppercase tracking-wider active:scale-[0.98]">
                            <template x-if="!loading">
                                <span class="flex items-center gap-2">
                                    <span>Buka Laporan Display</span>
                                    <i class="ph-bold ph-arrow-right"></i>
                                </span>
                            </template>
                            <template x-if="loading">
                                <span class="flex items-center gap-2">
                                    <i class="ph-bold ph-spinner animate-spin"></i>
                                    <span>Memverifikasi...</span>
                                </span>
                            </template>
                        </button>
                    </div>
                </form>

            </div>

            <!-- Footer Card Note -->
            <div class="bg-slate-50 border-t border-slate-100 px-6 py-4 text-center">
                <p class="text-[11px] text-slate-400 font-medium leading-relaxed">
                    Akses display ini diawasi oleh Departemen EHSS (Environment, Health, Safety & Security).
                </p>
            </div>

        </div>

    </main>

    <!-- Page Footer -->
    <footer class="w-full text-center py-4 text-xs font-semibold text-slate-400 relative z-10">
        &copy; {{ date('Y') }} EHSS Department — Environment, Health, Safety & Security. All rights reserved.
    </footer>

</body>
</html>
