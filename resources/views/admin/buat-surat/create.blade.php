<x-app-layout>
    <x-slot name="title">Buat Surat Langsung</x-slot>

    <div class="mb-4 flex items-center gap-2 text-sm text-gray-500">
        <a href="{{ route('admin.surat.index') }}" class="hover:text-brand-600">Antrian Surat</a>
        <span>/</span>
        <span class="text-gray-700">Buat Surat Langsung</span>
    </div>

    @php
        $mahasiswaMap = $mahasiswas->mapWithKeys(fn($m) => [
            $m->id => [
                'nama'    => $m->user->name,
                'nim'     => $m->nim,
                'angkatan'=> $m->angkatan,
                'alamat'  => $m->alamat ?? '',
            ]
        ])->toArray();
    @endphp

    <div class="flex gap-5 items-start"
         x-data="{
             jenis: '{{ old('jenis_surat', 'aktif_kuliah') }}',
             nomorSuffix: '{{ $nomorSuffix }}',
             mahasiswaMap: @js($mahasiswaMap),
             selectedMhs: '{{ old('mahasiswa_id') }}',
             nomorUrutan: '{{ old('nomor_urutan') }}',
             keperluan: '{{ old('keperluan') }}',
             keperluanManual: '{{ old('keperluan_manual') }}',
             tujuanInstansi: '{{ old('tujuan_instansi') }}',
             tanggal: '{{ old('tanggal_rencana') }}',
             waktu: '{{ old('waktu_rencana') }}',
             tempat: '{{ old('tempat') }}',
             judulSkripsi: '{{ old('judul_skripsi') }}',
             dosenPembimbingId: '{{ old('dosen_pembimbing_id') }}',
             dosenPenguji1Id: '{{ old('dosen_penguji_1_id') }}',
             dosenPenguji2Id: '{{ old('dosen_penguji_2_id') }}',
             namaInstansi: '{{ old('nama_instansi') }}',
             alamatInstansi: '{{ old('alamat_instansi') }}',
             tanggalMulai: '{{ old('tanggal_mulai') }}',
             tanggalSelesai: '{{ old('tanggal_selesai') }}',
             judulPenelitian: '{{ old('judul_penelitian') }}',
             bidangPenelitian: '{{ old('bidang_penelitian') }}',
             get mhsData() { return this.mahasiswaMap[this.selectedMhs] ?? null; },
             get nomorPenuh() { return (this.nomorUrutan || '...') + this.nomorSuffix; },
             previewBase: '{{ route('preview-surat') }}',
             get previewSrc() {
                 const mhs = this.mhsData;
                 const p = new URLSearchParams({
                     jenis:             this.jenis,
                     nomor_urut:        this.nomorUrutan || '',
                     nomor_surat:       this.nomorPenuh,
                     nama_mahasiswa:    mhs ? mhs.nama : '',
                     nim:               mhs ? mhs.nim : '',
                     angkatan:          mhs ? String(mhs.angkatan) : '',
                     alamat_mahasiswa:  mhs ? (mhs.alamat || '') : '',
                     keperluan:         this.keperluan === 'lainnya' ? (this.keperluanManual || '') : (this.keperluan || ''),
                     tujuan_instansi:   this.tujuanInstansi || '',
                     judul_skripsi:     this.judulSkripsi || '',
                     tanggal_seminar:   this.tanggal || '',
                     tanggal_sidang:    this.tanggal || '',
                     waktu_sidang:      this.waktu || '',
                     tempat_sidang:     this.tempat || '',
                     nama_instansi:     this.namaInstansi || '',
                     alamat_instansi:   this.alamatInstansi || '',
                     tanggal_mulai:     this.tanggalMulai || '',
                     tanggal_selesai:   this.tanggalSelesai || '',
                     judul_penelitian:  this.judulPenelitian || '',
                     bidang_penelitian: this.bidangPenelitian || '',
                 });
                 return this.previewBase + '?' + p.toString();
             },
         }"
         x-init="$watch('previewSrc', v => { if ($refs.previewFrame) $refs.previewFrame.src = v; })">

        {{-- ===================== KOLOM KIRI: Form ===================== --}}
        <div class="w-80 shrink-0">
            <div class="rounded-xl border bg-white p-5 shadow-sm">
                <h2 class="mb-4 text-sm font-semibold text-gray-800">Buat Surat Tanpa Pengajuan</h2>

                <form method="POST" action="{{ route('admin.buat-surat.store') }}" class="space-y-4">
                    @csrf

                    {{-- Pilih Mahasiswa --}}
                    <div>
                        <x-input-label for="mahasiswa_id" value="Mahasiswa *" />
                        <select id="mahasiswa_id" name="mahasiswa_id"
                                x-model="selectedMhs"
                                class="mt-1 block w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-brand-400 focus:ring-brand-400">
                            <option value="">-- Pilih Mahasiswa --</option>
                            @foreach ($mahasiswas as $mhs)
                                <option value="{{ $mhs->id }}" {{ old('mahasiswa_id') == $mhs->id ? 'selected' : '' }}>
                                    {{ $mhs->nim }} — {{ $mhs->user->name }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('mahasiswa_id')" class="mt-1" />
                    </div>

                    {{-- Jenis Surat --}}
                    <div>
                        <x-input-label for="jenis_surat" value="Jenis Surat *" />
                        <select id="jenis_surat" name="jenis_surat" x-model="jenis"
                                class="mt-1 block w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-brand-400 focus:ring-brand-400">
                            @foreach ($jenisTersedia as $kode => $label)
                                <option value="{{ $kode }}" {{ old('jenis_surat') === $kode ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Nomor Urutan --}}
                    <div>
                        <x-input-label for="nomor_urutan" value="Nomor Urutan Surat *" />
                        <div class="mt-1 flex items-center gap-0">
                            <input id="nomor_urutan" name="nomor_urutan" type="text"
                                   x-model="nomorUrutan"
                                   value="{{ old('nomor_urutan') }}"
                                   placeholder="2032"
                                   class="w-24 rounded-l-xl border border-r-0 border-slate-200 bg-white px-3 py-2 text-sm font-mono shadow-sm focus:border-brand-400 focus:ring-brand-400"
                                   required />
                            <span class="flex-1 truncate rounded-r-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-mono text-slate-500"
                                  x-text="nomorSuffix"></span>
                        </div>
                        <p class="mt-1 text-xs text-slate-400">
                            Preview: <span class="font-mono font-medium text-brand-600" x-text="nomorPenuh"></span>
                        </p>
                        <p class="text-[10px] text-slate-400">
                            Di template Word: <code class="rounded bg-slate-100 px-1 font-mono">${nomor_urut}/${kode_institusi}/${kode_prodi}/${bulan_surat}/${tahun_surat}</code>
                        </p>
                        <x-input-error :messages="$errors->get('nomor_urutan')" class="mt-1" />
                    </div>

                    {{-- Field: Aktif Kuliah --}}
                    <div x-show="jenis === 'aktif_kuliah'" x-cloak class="space-y-3">
                        <div>
                            <x-input-label for="keperluan" value="Keperluan *" />
                            <select id="keperluan" name="keperluan" x-model="keperluan"
                                    class="mt-1 block w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-brand-400 focus:ring-brand-400">
                                <option value="">-- Pilih Keperluan --</option>
                                <option value="Melamar Beasiswa">Melamar Beasiswa</option>
                                <option value="Magang / Praktik Kerja Lapangan (PKL)">Magang / PKL</option>
                                <option value="Administrasi Perbankan / Asuransi">Administrasi Perbankan / Asuransi</option>
                                <option value="Keperluan Akademik">Keperluan Akademik</option>
                                <option value="Pengurusan Visa / Pertukaran Pelajar">Pengurusan Visa / Pertukaran Pelajar</option>
                                <option value="lainnya">Lainnya (isi manual)</option>
                            </select>
                            <x-input-error :messages="$errors->get('keperluan')" class="mt-1" />
                        </div>
                        <div x-show="keperluan === 'lainnya'" x-cloak>
                            <x-input-label for="keperluan_manual" value="Sebutkan Keperluan *" />
                            <input id="keperluan_manual" name="keperluan_manual" type="text"
                                   x-model="keperluanManual"
                                   placeholder="Contoh: pengurusan visa pertukaran pelajar"
                                   class="mt-1 block w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-brand-400 focus:ring-brand-400" />
                        </div>
                        <div>
                            <x-input-label for="tujuan_instansi" value="Ditujukan Kepada" />
                            <input id="tujuan_instansi" name="tujuan_instansi" type="text"
                                   x-model="tujuanInstansi"
                                   placeholder="Contoh: Kepala Dinas Pendidikan Kota X"
                                   class="mt-1 block w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-brand-400 focus:ring-brand-400" />
                        </div>
                    </div>

                    {{-- Field: Seminar Proposal --}}
                    <div x-show="jenis === 'seminar_proposal'" x-cloak class="space-y-3">
                        <div>
                            <x-input-label for="sp_judul" value="Judul Skripsi *" />
                            <textarea id="sp_judul" name="judul_skripsi" rows="2" x-model="judulSkripsi"
                                      placeholder="Judul lengkap skripsi mahasiswa"
                                      class="mt-1 block w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-brand-400 focus:ring-brand-400"></textarea>
                        </div>
                        <div>
                            <x-input-label for="sp_pembimbing" value="Dosen Pembimbing *" />
                            <select id="sp_pembimbing" name="dosen_pembimbing_id" x-model="dosenPembimbingId"
                                    class="mt-1 block w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-brand-400 focus:ring-brand-400">
                                <option value="">-- Pilih Dosen Pembimbing --</option>
                                @foreach ($dosens as $dosen)
                                    <option value="{{ $dosen->id }}" {{ old('dosen_pembimbing_id') == $dosen->id ? 'selected' : '' }}>
                                        {{ $dosen->nama }}{{ $dosen->nip ? ' ('.$dosen->nip.')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="sp_penguji1" value="Dosen Penguji I *" />
                            <select id="sp_penguji1" name="dosen_penguji_1_id" x-model="dosenPenguji1Id"
                                    class="mt-1 block w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-brand-400 focus:ring-brand-400">
                                <option value="">-- Pilih Dosen Penguji I --</option>
                                @foreach ($dosens as $dosen)
                                    <option value="{{ $dosen->id }}" {{ old('dosen_penguji_1_id') == $dosen->id ? 'selected' : '' }}>
                                        {{ $dosen->nama }}{{ $dosen->nip ? ' ('.$dosen->nip.')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="sp_penguji2" value="Dosen Penguji II (opsional)" />
                            <select id="sp_penguji2" name="dosen_penguji_2_id" x-model="dosenPenguji2Id"
                                    class="mt-1 block w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-brand-400 focus:ring-brand-400">
                                <option value="">-- Pilih Dosen Penguji II --</option>
                                @foreach ($dosens as $dosen)
                                    <option value="{{ $dosen->id }}" {{ old('dosen_penguji_2_id') == $dosen->id ? 'selected' : '' }}>
                                        {{ $dosen->nama }}{{ $dosen->nip ? ' ('.$dosen->nip.')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="sp_tanggal" value="Tanggal Seminar *" />
                            <input id="sp_tanggal" name="tanggal_rencana" type="date" x-model="tanggal"
                                   class="mt-1 block w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-brand-400 focus:ring-brand-400" />
                        </div>
                        <div>
                            <x-input-label for="sp_waktu" value="Waktu" />
                            <input id="sp_waktu" name="waktu_rencana" type="text" x-model="waktu"
                                   placeholder="10.00 s/d selesai"
                                   class="mt-1 block w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-brand-400 focus:ring-brand-400" />
                        </div>
                        <div>
                            <x-input-label for="sp_tempat" value="Tempat / Ruangan" />
                            <input id="sp_tempat" name="tempat" type="text" x-model="tempat"
                                   placeholder="Ruang 01.03"
                                   class="mt-1 block w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-brand-400 focus:ring-brand-400" />
                        </div>
                    </div>

                    {{-- Field: Sidang Skripsi --}}
                    <div x-show="jenis === 'sidang_skripsi'" x-cloak class="space-y-3">
                        <div>
                            <x-input-label for="ss_judul" value="Judul Skripsi *" />
                            <textarea id="ss_judul" name="judul_skripsi" rows="2" x-model="judulSkripsi"
                                      placeholder="Judul lengkap skripsi mahasiswa"
                                      class="mt-1 block w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-brand-400 focus:ring-brand-400"></textarea>
                        </div>
                        <div>
                            <x-input-label for="ss_pembimbing" value="Dosen Pembimbing *" />
                            <select id="ss_pembimbing" name="dosen_pembimbing_id" x-model="dosenPembimbingId"
                                    class="mt-1 block w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-brand-400 focus:ring-brand-400">
                                <option value="">-- Pilih Dosen Pembimbing --</option>
                                @foreach ($dosens as $dosen)
                                    <option value="{{ $dosen->id }}" {{ old('dosen_pembimbing_id') == $dosen->id ? 'selected' : '' }}>
                                        {{ $dosen->nama }}{{ $dosen->nip ? ' ('.$dosen->nip.')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="ss_penguji1" value="Dosen Penguji I *" />
                            <select id="ss_penguji1" name="dosen_penguji_1_id" x-model="dosenPenguji1Id"
                                    class="mt-1 block w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-brand-400 focus:ring-brand-400">
                                <option value="">-- Pilih Dosen Penguji I --</option>
                                @foreach ($dosens as $dosen)
                                    <option value="{{ $dosen->id }}" {{ old('dosen_penguji_1_id') == $dosen->id ? 'selected' : '' }}>
                                        {{ $dosen->nama }}{{ $dosen->nip ? ' ('.$dosen->nip.')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="ss_penguji2" value="Dosen Penguji II (opsional)" />
                            <select id="ss_penguji2" name="dosen_penguji_2_id" x-model="dosenPenguji2Id"
                                    class="mt-1 block w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-brand-400 focus:ring-brand-400">
                                <option value="">-- Pilih Dosen Penguji II --</option>
                                @foreach ($dosens as $dosen)
                                    <option value="{{ $dosen->id }}" {{ old('dosen_penguji_2_id') == $dosen->id ? 'selected' : '' }}>
                                        {{ $dosen->nama }}{{ $dosen->nip ? ' ('.$dosen->nip.')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="ss_tanggal" value="Tanggal Sidang *" />
                            <input id="ss_tanggal" name="tanggal_rencana" type="date" x-model="tanggal"
                                   class="mt-1 block w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-brand-400 focus:ring-brand-400" />
                        </div>
                        <div>
                            <x-input-label for="ss_waktu" value="Waktu *" />
                            <input id="ss_waktu" name="waktu_rencana" type="text" x-model="waktu"
                                   placeholder="09.00 WIB"
                                   class="mt-1 block w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-brand-400 focus:ring-brand-400" />
                        </div>
                        <div>
                            <x-input-label for="ss_tempat" value="Tempat / Ruangan *" />
                            <input id="ss_tempat" name="tempat" type="text" x-model="tempat"
                                   placeholder="Ruang Sidang A"
                                   class="mt-1 block w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-brand-400 focus:ring-brand-400" />
                        </div>
                    </div>

                    {{-- Field: Undangan Penguji --}}
                    <div x-show="jenis === 'undangan_penguji'" x-cloak class="space-y-3">
                        <div>
                            <x-input-label for="up_judul" value="Judul Skripsi *" />
                            <textarea id="up_judul" name="judul_skripsi" rows="2" x-model="judulSkripsi"
                                      placeholder="Judul lengkap skripsi mahasiswa"
                                      class="mt-1 block w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-brand-400 focus:ring-brand-400"></textarea>
                        </div>
                        <div>
                            <x-input-label for="up_pembimbing" value="Dosen Pembimbing *" />
                            <select id="up_pembimbing" name="dosen_pembimbing_id" x-model="dosenPembimbingId"
                                    class="mt-1 block w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-brand-400 focus:ring-brand-400">
                                <option value="">-- Pilih Dosen Pembimbing --</option>
                                @foreach ($dosens as $dosen)
                                    <option value="{{ $dosen->id }}" {{ old('dosen_pembimbing_id') == $dosen->id ? 'selected' : '' }}>
                                        {{ $dosen->nama }}{{ $dosen->nip ? ' ('.$dosen->nip.')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="up_penguji1" value="Dosen Penguji I *" />
                            <select id="up_penguji1" name="dosen_penguji_1_id" x-model="dosenPenguji1Id"
                                    class="mt-1 block w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-brand-400 focus:ring-brand-400">
                                <option value="">-- Pilih Dosen Penguji I --</option>
                                @foreach ($dosens as $dosen)
                                    <option value="{{ $dosen->id }}" {{ old('dosen_penguji_1_id') == $dosen->id ? 'selected' : '' }}>
                                        {{ $dosen->nama }}{{ $dosen->nip ? ' ('.$dosen->nip.')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="up_penguji2" value="Dosen Penguji II (opsional)" />
                            <select id="up_penguji2" name="dosen_penguji_2_id" x-model="dosenPenguji2Id"
                                    class="mt-1 block w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-brand-400 focus:ring-brand-400">
                                <option value="">-- Pilih Dosen Penguji II --</option>
                                @foreach ($dosens as $dosen)
                                    <option value="{{ $dosen->id }}" {{ old('dosen_penguji_2_id') == $dosen->id ? 'selected' : '' }}>
                                        {{ $dosen->nama }}{{ $dosen->nip ? ' ('.$dosen->nip.')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="up_tanggal" value="Tanggal Sidang *" />
                            <input id="up_tanggal" name="tanggal_rencana" type="date" x-model="tanggal"
                                   class="mt-1 block w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-brand-400 focus:ring-brand-400" />
                        </div>
                        <div>
                            <x-input-label for="up_waktu" value="Waktu *" />
                            <input id="up_waktu" name="waktu_rencana" type="text" x-model="waktu"
                                   placeholder="09.00 WIB"
                                   class="mt-1 block w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-brand-400 focus:ring-brand-400" />
                        </div>
                        <div>
                            <x-input-label for="up_tempat" value="Tempat / Ruangan *" />
                            <input id="up_tempat" name="tempat" type="text" x-model="tempat"
                                   placeholder="Ruang Sidang A"
                                   class="mt-1 block w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-brand-400 focus:ring-brand-400" />
                        </div>
                    </div>

                    {{-- Field: Izin Magang --}}
                    <div x-show="jenis === 'izin_magang'" x-cloak class="space-y-3">
                        <div>
                            <x-input-label for="im_instansi" value="Nama Instansi *" />
                            <input id="im_instansi" name="nama_instansi" type="text" x-model="namaInstansi"
                                   placeholder="PT. Contoh Perusahaan"
                                   class="mt-1 block w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-brand-400 focus:ring-brand-400" />
                            <x-input-error :messages="$errors->get('nama_instansi')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="im_alamat" value="Alamat Instansi *" />
                            <textarea id="im_alamat" name="alamat_instansi" rows="2" x-model="alamatInstansi"
                                      placeholder="Jl. Contoh No. 1, Kota"
                                      class="mt-1 block w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-brand-400 focus:ring-brand-400"></textarea>
                        </div>
                        <div>
                            <x-input-label for="im_mulai" value="Tanggal Mulai *" />
                            <input id="im_mulai" name="tanggal_mulai" type="date" x-model="tanggalMulai"
                                   class="mt-1 block w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-brand-400 focus:ring-brand-400" />
                        </div>
                        <div>
                            <x-input-label for="im_selesai" value="Tanggal Selesai *" />
                            <input id="im_selesai" name="tanggal_selesai" type="date" x-model="tanggalSelesai"
                                   class="mt-1 block w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-brand-400 focus:ring-brand-400" />
                        </div>
                    </div>

                    {{-- Field: Rekomendasi Magang --}}
                    <div x-show="jenis === 'rekomendasi_magang'" x-cloak class="space-y-3">
                        <div>
                            <x-input-label for="rm_instansi" value="Nama Instansi *" />
                            <input id="rm_instansi" name="nama_instansi" type="text" x-model="namaInstansi"
                                   placeholder="PT. Contoh Perusahaan"
                                   class="mt-1 block w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-brand-400 focus:ring-brand-400" />
                            <x-input-error :messages="$errors->get('nama_instansi')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="rm_alamat" value="Alamat Instansi *" />
                            <textarea id="rm_alamat" name="alamat_instansi" rows="2" x-model="alamatInstansi"
                                      placeholder="Jl. Contoh No. 1, Kota"
                                      class="mt-1 block w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-brand-400 focus:ring-brand-400"></textarea>
                        </div>
                    </div>

                    {{-- Field: Izin Penelitian --}}
                    <div x-show="jenis === 'izin_penelitian'" x-cloak class="space-y-3">
                        <div>
                            <x-input-label for="ip_instansi" value="Nama Instansi *" />
                            <input id="ip_instansi" name="nama_instansi" type="text" x-model="namaInstansi"
                                   placeholder="Dinas / Lembaga Tujuan"
                                   class="mt-1 block w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-brand-400 focus:ring-brand-400" />
                        </div>
                        <div>
                            <x-input-label for="ip_alamat" value="Alamat Instansi *" />
                            <textarea id="ip_alamat" name="alamat_instansi" rows="2" x-model="alamatInstansi"
                                      placeholder="Jl. Contoh No. 1, Kota"
                                      class="mt-1 block w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-brand-400 focus:ring-brand-400"></textarea>
                        </div>
                        <div>
                            <x-input-label for="ip_judul" value="Judul Penelitian *" />
                            <textarea id="ip_judul" name="judul_penelitian" rows="2" x-model="judulPenelitian"
                                      placeholder="Analisis Pengaruh X terhadap Y"
                                      class="mt-1 block w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-brand-400 focus:ring-brand-400"></textarea>
                        </div>
                        <div>
                            <x-input-label for="ip_bidang" value="Bidang Penelitian *" />
                            <input id="ip_bidang" name="bidang_penelitian" type="text" x-model="bidangPenelitian"
                                   placeholder="Kesehatan Masyarakat"
                                   class="mt-1 block w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-brand-400 focus:ring-brand-400" />
                        </div>
                        <div>
                            <x-input-label for="ip_mulai" value="Tanggal Mulai *" />
                            <input id="ip_mulai" name="tanggal_mulai" type="date" x-model="tanggalMulai"
                                   class="mt-1 block w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-brand-400 focus:ring-brand-400" />
                        </div>
                        <div>
                            <x-input-label for="ip_selesai" value="Tanggal Selesai *" />
                            <input id="ip_selesai" name="tanggal_selesai" type="date" x-model="tanggalSelesai"
                                   class="mt-1 block w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-brand-400 focus:ring-brand-400" />
                        </div>
                    </div>

                    {{-- Tombol --}}
                    <div class="flex items-center justify-end gap-3 pt-1">
                        <a href="{{ route('admin.surat.index') }}"
                           class="rounded-lg border px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 transition-colors">Batal</a>
                        <button type="submit"
                                class="inline-flex items-center gap-2 rounded-xl bg-brand-500 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-600 transition-colors">
                            <x-icon name="file-cog" class="h-4 w-4" />
                            Buat & Generate
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ===================== KOLOM KANAN: Preview Surat ===================== --}}
        <div class="flex-1 min-w-0">
            <div class="mb-2 flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-widest text-gray-500">Pratinjau Surat</p>
                <p class="text-xs text-gray-400">Update otomatis saat form diisi</p>
            </div>
            <div class="overflow-hidden rounded-xl border bg-white shadow-sm" style="min-height: 297mm;">
                <iframe x-ref="previewFrame"
                        :src="previewSrc"
                        style="width: 100%; min-height: 297mm; border: none;"
                        title="Pratinjau Surat">
                </iframe>
            </div>
            <p class="mt-2 text-center text-xs text-gray-400">Preview dari template Word aktif</p>
        </div>

    </div>
</x-app-layout>
