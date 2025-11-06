<template>
  <div id="app" class="futuristic-container">
    <div class="bg-animation">
      <div class="stars"></div>
      <div class="clouds"></div>
    </div>

    <div class="main-content">
      <div class="title-container">
        <h1 class="magic-title">
          <span class="title-text">Material Generator</span>
        </h1>
        <p class="subtitle">Transformez vos URLs YouTube en listes de matériel</p>
      </div>

      <div class="card futuristic-card">
        <div v-if="error" class="error-message">
          <div class="error-text">{{ error }}</div>
          <button @click="clearError" class="error-close">×</button>
        </div>

        <div class="input-container">
          <div class="input-wrapper">
            <input
                v-model="youtubeUrl"
                @focus="animateInput = true"
                @blur="animateInput = false"
                @keyup.enter="generateList"
                type="text"
                placeholder="Collez votre URL YouTube ici ..."
                class="magic-input"
                :class="{ 'input-focused': animateInput, 'input-error': error }"
            />
            <div class="input-glow"></div>
            <div class="input-particles"></div>
          </div>
          <div v-if="!isValidUrl && youtubeUrl" class="url-warning">
            URL YouTube non valide
          </div>
        </div>

        <button
            @click="generateList"
            :disabled="loading || !youtubeUrl || !isValidUrl"
            class="magic-button"
            :class="{
            'button-loading': loading,
            'button-pulse': !loading && youtubeUrl && isValidUrl
          }"
        >
          <span class="button-text">
            {{ loading ? 'Génération en cours...' : 'Générer la Liste' }}
          </span>
          <div class="button-sparkles">
            <div class="sparkle"></div>
            <div class="sparkle"></div>
            <div class="sparkle"></div>
          </div>
          <div class="button-glow"></div>
        </button>

        <div v-if="loading" class="loading-container">
          <div class="magic-loader">
            <div class="orb"></div>
            <div class="orb"></div>
            <div class="orb"></div>
            <div class="loading-text">{{ loadingMessage }}</div>
          </div>
        </div>

        <div v-if="hasResults" class="results-container">
          <div class="results-header">
            <h3 class="results-title">Liste Générée :</h3>
          </div>

          <div v-if="results.materiaux && results.materiaux.length > 0" class="category-section">
            <h4 class="category-title">Matériaux</h4>
            <div class="results-list">
              <div v-for="(item, index) in results.materiaux" :key="'mat-'+index" class="result-item">
                <span class="item-number">{{ index + 1 }}</span>
                <span class="item-text">
                  <span class="typewriter-text" :style="`animation-delay: ${(index * 0.4) + 0.5}s; animation-duration: ${Math.max(item.length * 0.05, 0.8)}s`">
                    {{ item }}
                  </span>
                </span>
                <div class="item-glow"></div>
              </div>
            </div>
          </div>

          <div v-if="results.outils && results.outils.length > 0" class="category-section">
            <h4 class="category-title">Outils</h4>
            <div class="results-list">
              <div v-for="(item, index) in results.outils" :key="'outil-'+index" class="result-item">
                <span class="item-number">{{ index + 1 }}</span>
                <span class="item-text">
                  <span class="typewriter-text" :style="`animation-delay: ${(results.materiaux.length * 0.4) + (index * 0.4) + 1}s; animation-duration: ${Math.max(item.length * 0.05, 0.8)}s`">
                    {{ item }}
                  </span>
                </span>
                <div class="item-glow"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { urlVideoService } from '@/services/urlVideoService';
import '../styles/MaterialGenerator.css';

export default {
  name: 'App',
  data() {
    return {
      youtubeUrl: '',
      loading: false,
      animateInput: false,
      error: null,
      results: {
        materiaux: [],
        outils: [],
        debug: null
      },
      loadingStages: [
        'Connexion à YouTube...',
        'Extraction du contenu...',
        'Analyse en cours...',
        'Génération de la liste...'
      ],
      currentLoadingStage: 0,
      isTyping: false
    }
  },
  computed: {
    isValidUrl() {
      const youtubeRegex = /^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.?be)\/.+$/;
      return youtubeRegex.test(this.youtubeUrl);
    },
    loadingMessage() {
      return this.loadingStages[this.currentLoadingStage] || 'Traitement en cours...';
    },
    hasResults() {
      return (this.results.materiaux && this.results.materiaux.length > 0) ||
          (this.results.outils && this.results.outils.length > 0);
    }
  },
  methods: {
    async generateList() {
      if (!this.isValidUrl) {
        this.error = "Veuillez entrer une URL YouTube valide";
        return;
      }

      this.loading = true;
      this.error = null;
      this.results = {
        materiaux: [],
        outils: [],
        debug: null
      };
      this.currentLoadingStage = 0;
      this.isTyping = false;

      const progressInterval = setInterval(() => {
        if (this.currentLoadingStage < this.loadingStages.length - 1) {
          this.currentLoadingStage++;
        }
      }, 800);

      try {
        const response = await urlVideoService.generateList(this.youtubeUrl);

        if (response.data && response.data['analysis']) {
          this.results = {
            materiaux: response.data['analysis'].materiaux,
            outils: response.data['analysis'].outils,
            debug: response.data.debug || null
          };
        } else if (response['analysis']) {
          this.results = {
            materiaux: response['analysis'].materiaux,
            outils: response['analysis'].outils,
            debug: response.debug || null
          };
        } else {
          this.results = {
            materiaux: response.data?.materiaux || response.materiaux,
            outils: response.data?.outils || response.outils,
            debug: response.data?.debug || response.debug
          };
        }

        setTimeout(() => {
          this.isTyping = true;
        }, 300);

        this.youtubeUrl = "";

        this.showSuccessMessage('Liste générée avec succès !');

      } catch (err) {
        this.error = err.message || 'Une erreur est survenue lors de la génération de la liste !';
      } finally {
        clearInterval(progressInterval);
        this.loading = false;
        this.currentLoadingStage = 0;
      }
    },

    clearError() {
      this.error = null;
    },

    showSuccessMessage(message) {
      console.log(message);
    }
  }
}

</script>

<style scoped>
</style>
