# Kogi State Rural Electrification Needs Assessment and Planning Platform (REMP)

**Project Name:** Kogi State Rural Electrification Decision-Support Platform  
**Initial Implementation Scope:** Three Selected LGAs in Kogi State (`Okene`, `Idah`, `Kabba/Bunu`)  
**Document Version:** 1.0  

---

## 1. Overview & Functional Architecture

The **Kogi State Rural Electrification Decision-Support Platform** is an integrated full-stack Laravel web application built to answer five core rural electrification planning questions:
1. **Where is electricity most needed?** (Interactive GIS map + participatory public reporting)
2. **Who is affected?** (Verified field assessments of households, schools, healthcare facilities, and SMEs)
3. **How much electricity is required?** (Automated appliance-level energy demand & peak load calculation engine)
4. **What renewable energy solution is suitable?** (Rule-based renewable energy intervention recommender)
5. **How much will the intervention cost?** (Indicative 9-component CAPEX cost estimator in Nigerian Naira `₦` + multi-criteria prioritisation & PDF/Excel reporting)

### Seven Integrated Functional Categories
| Category | Module | Key Capabilities |
| :--- | :--- | :--- |
| **1** | **Dashboard & Overview** | KPIs across the 3 LGAs, registered/verified/assessed communities, affected households & facilities, Chart.js status doughnut chart, LGA daily demand bar chart, proposed interventions table, priority tiers, and Livewire real-time demand simulator. |
| **2** | **Geographic & Community Management** | Configurable LGAs, Wards, and Communities with GPS coordinates (`latitude`, `longitude`) rendered on an interactive **Leaflet.js GIS map** with FRD color indicators (**Red**: No electricity, **Orange**: Unreliable/Inadequate, **Green**: Reliable, **Blue Ring**: Proposed intervention, **Dashed Marker**: Unverified public report). |
| **3** | **Public Electricity Reporting Portal** | No authentication required. Dynamic LGA/Ward/Community selection, interactive map pin & browser GPS auto-detect, photo upload, 24-hour duplicate detection flag, unique tracking reference (`KOGI-2026-XXXXX`), and public status tracking page. |
| **4** | **Field Data Collection & Verification** | LGA Officer verification queue (`submitted` -> `under_review` -> `verified` / `rejected` -> `assessed`), officer assignment, and mobile-first 5-step Field Assessment form (Community, Households, Schools, Health Facilities, Businesses, Appliances) with **Offline Draft Saving (`localStorage` + PWA Service Worker)**. |
| **5** | **Energy Needs Assessment** | Appliance library with rated wattages, custom equipment support, automated daily consumption (`Wh/day` & `kWh/day`) and peak load (`kW`) calculations, plus Energy Analyst assumption review with an immutable audit log. |
| **6** | **Renewable Energy Recommendation & Cost Estimation** | Evaluates total demand, settlement density, and facility mix to recommend `Solar Home Systems`, `Solar Hybrid Mini-Grid`, `Institutional Solar Systems`, `Solar Water Pumping`, or `Productive-Use Solar Systems` with 9-component **Indicative Cost Estimates in Nigerian Naira (NGN)**. |
| **7** | **Planning, Prioritisation & Reporting** | Multi-criteria weighted scoring algorithm (`High`, `Medium`, `Low` priority tiers) and one-click export of Community Dossiers, LGA Reports, and Priority Investment Reports in **PDF (`barryvdh/laravel-dompdf`)** and **Excel/CSV (`maatwebsite/excel`)**. |

---

## 2. Setup & Installation Instructions

### Prerequisites
- PHP >= 8.2
- Composer 2.x
- MySQL 8.0+ (or SQLite for rapid local evaluation)

### Quick Start
1. **Configure Environment:**
   Copy `.env.example` to `.env` (already configured in this workspace). For MySQL production deployment, set:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=kogi_remp
   DB_USERNAME=root
   DB_PASSWORD=
   ```
   *(By default, `.env` is pre-configured with `DB_CONNECTION=sqlite` pointing to `database/database.sqlite` so the platform runs out-of-the-box without external database setup.)*

2. **Install Dependencies & Generate Application Key:**
   ```bash
   composer install
   php artisan key:generate
   ```

3. **Run Migrations & Seed Demo Data (3 LGAs, 15 Wards, 150 Communities, 45 Full Assessments):**
   ```bash
   php artisan migrate:fresh --seed
   ```

4. **Start the Development Server:**
   ```bash
   php artisan serve
   ```
   Visit `http://127.0.0.1:8000` in your browser.

---

## 3. Default Seeded User Accounts & Roles

All seeded accounts use the password: `password`  
*(The `/login` page also includes a **One-Click Role Demonstration Switcher** so evaluators can switch roles immediately.)*

| Role | Official Name | Email | Password | Responsibilities |
| :--- | :--- | :--- | :--- | :--- |
| **System Administrator** | Engr. Suleiman Adavize | `admin@kogiremp.gov.ng` | `password` | Full access to all 7 categories, LGA/Ward/Community CRUD, user role management. |
| **LGA Officer** | Hon. Momohjimoh Bello | `lga@kogiremp.gov.ng` | `password` | Reviews & verifies/rejects public electricity reports (`Okene` LGA default), assigns field officers. |
| **Field Data Collector** | Yakubu Ojonugwa | `field@kogiremp.gov.ng` | `password` | Conducts 5-step community & facility field assessments with offline draft saving. |
| **Energy Analyst** | Dr. Funmilayo Olorunfemi | `analyst@kogiremp.gov.ng` | `password` | Reviews & overrides energy demand assumptions, renewable recommendations, and NGN cost estimates. |
| **Stakeholder Viewer** | Permanent Secretary Ministry of Energy | `stakeholder@kogiremp.gov.ng` | `password` | Read-only executive access to dashboards, GIS maps, prioritisation rankings, and PDF/Excel exports. |
| **Public Resident** | *(No login required)* | Visit `/report-challenge` | — | Submits community electricity challenges and tracks status via reference number at `/track-report`. |

---

## 4. Engineering Calculation & Prioritisation Methodology

### A. Energy Demand Calculation (`App\Services\EnergyCalculationService`)
- **Equipment Daily Energy (`Wh/day`):**
  $$\text{Energy (Wh/day)} = \text{Rated Power (W)} \times \text{Quantity} \times \text{Operating Hours/Day}$$
- **Total Community Energy Demand (`kWh/day`):**
  $$\text{Total Daily Demand (kWh/day)} = \frac{\sum \text{Energy (Wh/day)}}{1000} \times (1 + \text{Distribution Loss Margin})$$
  *(Default Distribution/Inverter Loss Margin = `10%`, adjustable by Energy Analyst)*
- **Estimated Peak Load (`kW`):**
  $$\text{Peak Load (kW)} = \frac{\sum (\text{Rated Power (W)} \times \text{Quantity})}{1000} \times \text{Simultaneous Usage Factor}$$
  *(Default Coincidence / Simultaneous Usage Factor = `0.70`, adjustable with audit trail)*

### B. Renewable Sizing & Indicative Cost Estimation (`App\Services\RecommendationAndCostService`)
- **PV Array Capacity (`kWp`):** Sized using Kogi State's average solar insolation profile of **4.6 Peak Sun Hours (PSH)** and a **0.78 system efficiency factor**.
- **Battery Storage (`kWh`):** Sized for **LiFePO4 (85% Depth of Discharge)** covering evening/night autonomy (`65%` load fraction).
- **9 Cost Components (Labelled Indicative in Nigerian Naira `₦`):**
  1. Solar PV Panels (550W Monocrystalline)
  2. LiFePO4 Battery Storage Bank
  3. Hybrid Inverters
  4. MPPT Charge Controllers
  5. Galvanized Mounting Structures
  6. LV Distribution Infrastructure & Smart Metering / SHS Kits
  7. DC/AC Protection Equipment & Earthing
  8. Civil Works, Installation & Commissioning (`12%`)
  9. Logistics & Transportation to Rural Kogi LGA Site (`5%`)

### C. Multi-Criteria Prioritisation Algorithm (`App\Services\PrioritisationService`)
Each community receives a composite score ($0 - 100$) based on the five FRD weighted criteria:
1. **Affected Households Without Electricity:** `30%` weight
2. **Affected Schools & Healthcare Facilities (Vaccine Cold-Chain & Maternity Urgency):** `25%` weight
3. **Economic Activities & Livelihood Impact (Grain Mills, Welding, Cold Storage SMEs):** `20%` weight
4. **Total Electricity Demand (`kWh/day`):** `15%` weight
5. **Technical Feasibility & Verification Readiness:** `10%` weight

- **High Priority:** Score $\ge 80$
- **Medium Priority:** Score $50 - 79.99$
- **Low Priority:** Score $< 50$
