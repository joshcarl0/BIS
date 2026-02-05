document.addEventListener("DOMContentLoaded", () => {

  // ✅ REQUIRED: register datalabels plugin (Chart.js v3/v4)
  if (window.Chart && window.ChartDataLabels) {
    Chart.register(ChartDataLabels);
  }

  const d = window.DASHBOARD_DATA || {};
  const el = (id) => document.getElementById(id);

  // AGE PIE
  if (el("agePie")) {
    new Chart(el("agePie"), {
      type: "pie",
      data: {
        labels: ["Minors (0-17)", "Adults (18-59)", "Seniors (60+)"],
        datasets: [{
          data: [d.age?.minors || 0, d.age?.adults || 0, d.age?.seniors || 0]
        }]
      }
    });
  }

  // GENDER BAR
  if (el("genderBar")) {
    new Chart(el("genderBar"), {
      type: "bar",
      data: {
        labels: ["Male", "Female"],
        datasets: [{
          label: "Residents",
          data: [d.gender?.male || 0, d.gender?.female || 0]
        }]
      },
      options: {
        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
      }
    });
  }

  // SPECIAL GROUPS BAR
  if (el("specialChart")) {
    new Chart(el("specialChart"), {
      type: "bar",
      data: {
        labels: d.special?.labels || [],
        datasets: [{
          label: "Special Groups",
          data: d.special?.data || []
        }]
      },
      options: {
        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
      }
    });
  }

  // ✅ SES PIE (with % labels like your sample)
  if (el("sesPie")) {
    const labels = d.ses?.labels || [];
    const values = (d.ses?.data || []).map(v => Number(v) || 0);
    const total = values.reduce((a, b) => a + b, 0);

    new Chart(el("sesPie"), {
      type: "pie",
      data: {
        labels,
        datasets: [{ data: values }]
      },
      options: {
        plugins: {
          legend: { position: "right" },
          datalabels: {
            color: "#fff",
            font: { weight: "bold", size: 14 },
            formatter: (value) => {
              if (!total || value <= 0) return "";
              const pct = Math.round((value / total) * 100);
              return pct + "%";
            }
          }
        }
      }
    });
  }

});
