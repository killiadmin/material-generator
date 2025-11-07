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
                ref="youtubeInput"
                v-model="videoUrl"
                @focus="animateInput = true"
                @blur="animateInput = false"
                @keyup.enter="generateList"
                type="text"
                placeholder="Collez votre URL YouTube ici ..."
                class="magic-input"
                :class="{ 'input-focused': animateInput, 'input-error': error }"
            />

            <div v-if="previewLoading" class="preview-loading">
              Chargement de la vidéo...
            </div>

            <div v-if="videoPreview && !previewLoading" class="video-preview">
              <img
                  :src="videoPreview['thumbnail']"
                  alt="Miniature"
                  class="preview-thumbnail"
              />
              <div class="preview-details">
                <h3 class="preview-title">{{ videoPreview.title }}</h3>
                <p class="preview-channel">{{ videoPreview['channelTitle'] }}</p>
              </div>
            </div>

            <div class="input-actions">
              <button
                  @click="pasteFromClipboard"
                  class="icon-button"
                  :disabled="pasting"
                  title="Coller depuis le presse-papier"
              >
                <template v-if="!pasted">📋</template>
              </button>

              <button v-if="videoUrl" @click="clearInput" class="icon-button clear-button" title="Effacer le contenu">
                ❌
              </button>
            </div>
          </div>

          <div v-if="!isValidUrl && videoUrl" class="url-warning">
            URL YouTube non valide
          </div>

          <div v-if="pasteError" class="paste-error">
            {{ pasteError }}
          </div>
        </div>


        <button
            @click="generateList"
            :disabled="loading || !videoUrl || !isValidUrl"
            class="magic-button"
            :class="{
            'button-loading': loading,
            'button-pulse': !loading && videoUrl && isValidUrl
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
            <button @click="saveToFile" class="action-button save-button" :class="{ 'save-loading': saveLoading }" title="Télécharger en fichier texte">
              <span class="button-icon">{{ saveLoading ? '⏳' : '💾' }}</span>
              <span class="button-text">
          {{ saveLoading ? 'Génération...' : 'Enregistrer' }}
        </span>
            </button>
            <h3 class="results-title">Liste Générée :</h3>
            <button @click="copyToClipboard" class="copy-button" :class="{ 'copy-success': copySuccess }" :title="copySuccess ? 'Copié !' : 'Copier la liste'">
              <span class="copy-icon">{{ copySuccess ? '✅' : '📋' }}</span>
              <span class="copy-text">
        {{ copySuccess ? 'Copié !' : 'Copier' }}
      </span>
            </button>
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
import { videoPreviewService } from '@/services/videoPreviewService';
import '../styles/MaterialGenerator.css';

export default {
  name: 'App',
  data() {
    return {
      videoUrl: '',
      loading: false,
      animateInput: false,
      error: null,
      pasting: false,
      pasted: false,
      pasteError: null,
      videoPreview: null,
      previewLoading: false,
      copySuccess: false,
      copyTimeout: null,
      saveLoading: false,
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
      return youtubeRegex.test(this.videoUrl);
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
    /**
     *
     * @returns {Promise<void>}
     */
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
        const response = await urlVideoService.generateList(this.videoUrl);

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

        this.videoUrl = "";

      } catch (err) {
        this.error = err.message || 'Une erreur est survenue lors de la génération de la liste !';
      } finally {
        clearInterval(progressInterval);
        this.loading = false;
        this.currentLoadingStage = 0;
      }
    },

    /**
     *
     * @returns {Promise<void>}
     */
    async pasteFromClipboard() {
      this.pasteError = null;
      this.pasted = false;
      this.pasting = true;

      if (!navigator.clipboard || !navigator.clipboard.readText) {
        this.pasteError = "API clipboard non supportée par ce navigateur.";
        this.pasting = false;
        return;
      }

      try {
        const text = await navigator.clipboard.readText();

        if (!text) {
          this.pasteError = "Le presse-papier est vide.";
          this.pasting = false;
          return;
        }

        this.videoUrl = text;
        this.pasted = true;
      } catch (err) {
        this.pasteError = err?.message || "Permission refusée ou erreur lors de la lecture.";
      } finally {
        this.pasting = false;
      }
    },

    /**
     *
     */
    clearInput() {
      this.videoUrl = '';
      this.pasteError = null;
      this.pasted = false;
      this.$nextTick(() => {
        const el = this.$refs.youtubeInput;
        if (el && el.focus) el.focus();
      });
    },

    /**
     *
     */
    clearError() {
      this.error = null;
    },

    /**
     * Formats a material and tools list into a structured string suitable for file storage or display.
     * The method includes headings, separators, numbered items, and a generated footer with a timestamp.
     *
     * @return {string}
     */
    formatListForFile() {
      let fileContent = 'LISTE DE MATÉRIEL ET OUTILS\n';
      fileContent += '='.repeat(40) + '\n\n';

      if (this.videoUrl) {
        fileContent += `Source: ${this.videoUrl}\n\n`;
      }

      if (this.results.materiaux && this.results.materiaux.length > 0) {
        fileContent += 'MATÉRIAUX :\n';
        fileContent += '-'.repeat(20) + '\n';
        this.results.materiaux.forEach((item, index) => {
          fileContent += `${index + 1}. ${item}\n`;
        });
        fileContent += '\n';
      }

      if (this.results.outils && this.results.outils.length > 0) {
        fileContent += 'OUTILS :\n';
        fileContent += '-'.repeat(20) + '\n';
        this.results.outils.forEach((item, index) => {
          fileContent += `${index + 1}. ${item}\n`;
        });
        fileContent += '\n';
      }

      fileContent += '\n' + '='.repeat(40) + '\n';
      fileContent += `Généré le : ${new Date().toLocaleDateString('fr-FR', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })}`;
      fileContent += '\n';
      fileContent += 'Créé avec Material Generator';

      return fileContent;
    },

    /**
     * Saves a formatted list to a text file for download.
     *
     * @return {Promise<void>}
     */
    async saveToFile() {
      this.saveLoading = true;

      try {
        const fileContent = this.formatListForFile();

        const blob = new Blob([fileContent], {
          type: 'text/plain;charset=utf-8'
        });

        const url = URL.createObjectURL(blob);

        const link = document.createElement('a');
        link.href = url;

        const timestamp = new Date().toISOString().slice(0, 19).replace(/:/g, '-');
        link.download = "liste-materiaux-" + timestamp + ".txt";

        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        URL.revokeObjectURL(url);

      } catch (err) {
        this.showErrorMessage(err);
      } finally {
        this.saveLoading = false;
      }
    },

    /**
     * Copies a formatted list to the clipboard.
     *
     * @return {Promise<void>}
     */
    async copyToClipboard() {
      if (this.copyTimeout) {
        clearTimeout(this.copyTimeout);
      }

      try {
        const formattedList = this.formatListForCopy();

        if (navigator.clipboard && navigator.clipboard.writeText) {
          await navigator.clipboard.writeText(formattedList);
        } else {
          this.fallbackCopyToClipboard(formattedList);
        }

        this.copySuccess = true;

        this.copyTimeout = setTimeout(() => {
          this.copySuccess = false;
        }, 3000);

      } catch (err) {
        this.showErrorMessage(err);
      }
    },

    /**
     * Formats a list of materials and tools into a text string.
     *
     * @return {string}
     */
    formatListForCopy() {
      let formattedText = 'Liste de Matériel et Outils\n\n';

      if (this.results.materiaux && this.results.materiaux.length > 0) {
        formattedText += 'MATÉRIAUX :\n';
        this.results.materiaux.forEach((item, index) => {
          formattedText += `${index + 1}. ${item}\n`;
        });
        formattedText += '\n';
      }

      if (this.results.outils && this.results.outils.length > 0) {
        formattedText += 'OUTILS :\n';
        this.results.outils.forEach((item, index) => {
          formattedText += `${index + 1}. ${item}\n`;
        });
      }

      return formattedText;
    },

    /**
     * Fallback method for copying text to the clipboard.
     *
     * @param text
     */
    fallbackCopyToClipboard(text) {
      const textArea = document.createElement('textarea');
      textArea.value = text;
      textArea.style.position = 'fixed';
      textArea.style.left = '-999999px';
      textArea.style.top = '-999999px';
      document.body.appendChild(textArea);
      textArea.focus();
      textArea.select();

      try {
        document.execCommand('copy');
      } catch (err) {
        throw new Error(err);
      } finally {
        document.body.removeChild(textArea);
      }
    },

    /**
     * Shows an error message in the UI.
     *
     * @param message
     */
    showErrorMessage(message) {
      this.error = message;
    }
  },
  watch: {

    /**
     * Fetches and sets a video preview based on the given URL if the URL is valid.
     *
     * @param {string} newUrl
     * @return {Promise<void>}
     */
    async videoUrl(newUrl) {
      this.videoPreview = null;

      if (!this.isValidUrl){
        return;
      }

      this.previewLoading = true;
      try {
        const data = await videoPreviewService.fetchPreview(newUrl);

        if (data && !data['error']) {
          this.videoPreview = data;
        } else {
          this.videoPreview = null;
        }
      } catch (e) {
        this.videoPreview = null;
      } finally {
        this.previewLoading = false;
      }
    }
  }
}
</script>

<style scoped>
</style>
