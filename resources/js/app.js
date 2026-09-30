import './bootstrap';

// Alpine.js dikelola SEPENUHNYA oleh Livewire v4.
// @livewireScripts di app.blade.php sudah meng-inject Alpine secara otomatis.
// JANGAN load Alpine di sini — menyebabkan "multiple instances" error.

// Chart.js — expose ke window agar bisa dipakai di blade inline script
import { Chart, BarController, BarElement, CategoryScale, LinearScale, Tooltip, Legend } from 'chart.js';
Chart.register(BarController, BarElement, CategoryScale, LinearScale, Tooltip, Legend);
window.Chart = Chart;
