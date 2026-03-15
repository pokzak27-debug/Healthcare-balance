import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('availableSlots', () => ({
  times: [],
  loading: false,
  init() {
    const dateEl = document.getElementById('appointment_date');
    const poleEl = document.getElementById('pole_id');
    const userEl = document.getElementById('user_id');
    const durationEl = document.getElementById('duration_minutes');
    const timeEl = document.getElementById('appointment_time');
    const fetchSlots = () => {
      const date = dateEl?.value;
      const poleId = poleEl?.value;
      if (!date || !poleId) {
        this.times = [];
        return;
      }
      this.loading = true;
      const params = new URLSearchParams({ date, pole_id: poleId });
      if (userEl?.value) params.set('user_id', userEl.value);
      if (durationEl?.value) params.set('duration_minutes', durationEl.value);
      fetch(`/appointments/available-slots?${params}`)
        .then(r => r.json())
        .then(data => { this.times = data.times || []; this.loading = false; })
        .catch(() => { this.times = []; this.loading = false; });
    };
    [dateEl, poleEl, userEl, durationEl].filter(Boolean).forEach(el => el?.addEventListener('change', fetchSlots));
  },
  select(time) {
    const el = document.getElementById('appointment_time');
    if (el) el.value = time;
  }
}));

Alpine.start();
