import { startStimulusApp } from '@symfony/stimulus-bundle';

const app = startStimulusApp();

// register any custom, 3rd party controllers here
import EntrepriseChartsController from './controllers/entreprise_charts_controller.js';
app.register('entreprise-charts', EntrepriseChartsController);

import EntrepriseStatsController from './controllers/entreprise_stats_controller.js';
app.register('entreprise-stats', EntrepriseStatsController);
