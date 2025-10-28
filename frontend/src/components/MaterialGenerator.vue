<template>
  <div id="app" class="futuristic-container">
    <!-- Effets d'arrière-plan -->
    <div class="bg-animation">
      <div class="stars"></div>
      <div class="clouds"></div>
    </div>

    <div class="main-content">
      <!-- Titre avec animation -->
      <div class="title-container">
        <h1 class="magic-title">
          <span class="title-text">Material generator</span>
          <span class="title-glow"></span>
        </h1>
        <p class="subtitle">Transformez vos URLs YouTube </p>
      </div>

      <!-- Carte principale -->
      <div class="card futuristic-card">
        <!-- Input avec animation -->
        <div class="input-container">
          <div class="input-wrapper">
            <input
                v-model="youtubeUrl"
                @focus="animateInput = true"
                @blur="animateInput = false"
                type="text"
                placeholder="Collez votre URL YouTube ici..."
                class="magic-input"
                :class="{ 'input-focused': animateInput }"
            />
            <div class="input-glow"></div>
            <div class="input-particles"></div>
          </div>
        </div>

        <!-- Bouton avec effet magique -->
        <button
            @click="generateList"
            :disabled="loading || !youtubeUrl"
            class="magic-button"
            :class="{ 'button-loading': loading, 'button-pulse': !loading && youtubeUrl }"
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

        <!-- Animation de chargement -->
        <div v-if="loading" class="loading-container">
          <div class="magic-loader">
            <div class="orb"></div>
            <div class="orb"></div>
            <div class="orb"></div>
            <div class="loading-text">Analyse de la vidéo en cours...</div>
          </div>
        </div>

        <!-- Résultats -->
        <div v-if="results.length > 0" class="results-container">
          <h3 class="results-title">Liste Générée :</h3>
          <div class="results-list">
            <div
                v-for="(item, index) in results"
                :key="index"
                class="result-item"
                :style="`animation-delay: ${index * 0.1}s`"
            >
              <span class="item-number">{{ index + 1 }}</span>
              <span class="item-text">{{ item }}</span>
              <div class="item-glow"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'App',
  data() {
    return {
      youtubeUrl: '',
      loading: false,
      animateInput: false,
      results: []
    }
  },
  methods: {
    async generateList() {
      if (!this.youtubeUrl) return;

      this.loading = true;
      this.results = [];

      // Simulation du traitement
      await new Promise(resolve => setTimeout(resolve, 3000));

      // Résultats simulés
      this.results = [
        'Introduction aux concepts magiques',
        'Démonstration des pouvoirs',
        'Exercices pratiques',
        'Conseils avancés',
        'Ressources supplémentaires',
        'Communauté de sorciers modernes'
      ];

      this.loading = false;
    }
  }
}
</script>

<style scoped>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  margin: 0;
}

.futuristic-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
  position: relative;
  overflow: hidden;
  font-family: 'Arial', sans-serif;
}

/* Animations d'arrière-plan */
.bg-animation {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 1;
}

.stars {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: transparent;
  animation: stars 200s linear infinite;
}

.clouds {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: transparent url('https://s3-us-west-2.amazonaws.com/s.cdpn.io/1231630/clouds.png') repeat;
  animation: clouds 200s linear infinite;
}

@keyframes stars {
  0% { transform: translateY(0); }
  100% { transform: translateY(-2000px); }
}

@keyframes clouds {
  0% { transform: translateY(0); }
  100% { transform: translateY(-2000px); }
}

.main-content {
  position: relative;
  z-index: 2;
  padding: 2rem;
  max-width: 800px;
  margin: 0 auto;
}

/* Titre */
.title-container {
  text-align: center;
  margin-bottom: 3rem;
}

.magic-title {
  position: relative;
  font-size: 3.5rem;
  font-weight: 800;
  background: linear-gradient(45deg, #ff6b6b, #feca57, #48dbfb, #ff9ff3);
  background-size: 400% 400%;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  animation: gradientShift 3s ease infinite;
  margin-bottom: 1rem;
}

.title-glow {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 120%;
  height: 120%;
  background: inherit;
  filter: blur(20px);
  opacity: 0.3;
  z-index: -1;
}

.subtitle {
  font-size: 1.2rem;
  color: rgba(255, 255, 255, 0.7);
  animation: fadeIn 2s ease;
}

/* Carte */
.futuristic-card {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 20px;
  padding: 3rem;
  box-shadow:
      0 20px 40px rgba(0, 0, 0, 0.3),
      inset 0 1px 0 rgba(255, 255, 255, 0.1);
  animation: cardAppear 1s ease;
}

/* Input */
.input-container {
  margin-bottom: 2rem;
}

.input-wrapper {
  position: relative;
}

.magic-input {
  width: 100%;
  padding: 1.5rem 2rem;
  font-size: 1.1rem;
  background: rgba(255, 255, 255, 0.05);
  border: 2px solid transparent;
  border-radius: 15px;
  color: white;
  transition: all 0.3s ease;
  position: relative;
  z-index: 2;
}

.magic-input::placeholder {
  color: rgba(255, 255, 255, 0.5);
}

.magic-input:focus {
  outline: none;
  border-color: #48dbfb;
  box-shadow: 0 0 30px rgba(72, 219, 251, 0.3);
}

.input-glow {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(45deg, #ff6b6b, #48dbfb, #ff9ff3);
  border-radius: 15px;
  filter: blur(15px);
  opacity: 0;
  transition: opacity 0.3s ease;
  z-index: 1;
}

.input-focused + .input-glow {
  opacity: 0.3;
}

.input-particles {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
}

/* Bouton */
.magic-button {
  position: relative;
  width: 100%;
  padding: 1.5rem 2rem;
  font-size: 1.2rem;
  background: linear-gradient(45deg, #ff6b6b, #ff9ff3);
  border: none;
  border-radius: 15px;
  color: white;
  cursor: pointer;
  transition: all 0.3s ease;
  overflow: hidden;
  font-weight: 600;
}

.magic-button:not(:disabled):hover {
  transform: translateY(-2px);
  box-shadow: 0 15px 30px rgba(255, 107, 107, 0.4);
}

.magic-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.button-pulse {
  animation: pulse 2s infinite;
}

.button-loading .button-text {
  opacity: 0.7;
}

.button-sparkles {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
}

.sparkle {
  position: absolute;
  width: 4px;
  height: 4px;
  background: white;
  border-radius: 50%;
  animation: sparkle 2s infinite;
}

.sparkle:nth-child(1) { top: 20%; left: 10%; animation-delay: 0s; }
.sparkle:nth-child(2) { top: 60%; left: 80%; animation-delay: 0.5s; }
.sparkle:nth-child(3) { top: 30%; left: 50%; animation-delay: 1s; }

.button-glow {
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
  transition: left 0.5s ease;
}

.magic-button:hover .button-glow {
  left: 100%;
}

/* Loader */
.loading-container {
  margin: 2rem 0;
}

.magic-loader {
  text-align: center;
  position: relative;
}

.orb {
  display: inline-block;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  margin: 0 10px;
  animation: orbFloat 2s ease-in-out infinite;
}

.orb:nth-child(1) {
  background: #ff6b6b;
  animation-delay: 0s;
}

.orb:nth-child(2) {
  background: #48dbfb;
  animation-delay: 0.2s;
}

.orb:nth-child(3) {
  background: #ff9ff3;
  animation-delay: 0.4s;
}

.loading-text {
  margin-top: 1rem;
  color: rgba(255, 255, 255, 0.7);
  font-size: 1rem;
  animation: fadeInOut 2s infinite;
}

/* Résultats */
.results-container {
  margin-top: 2rem;
  animation: slideUp 0.5s ease;
}

.results-title {
  color: white;
  margin-bottom: 1.5rem;
  font-size: 1.5rem;
  text-align: center;
}

.results-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.result-item {
  position: relative;
  background: rgba(255, 255, 255, 0.05);
  padding: 1.5rem;
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  align-items: center;
  animation: itemAppear 0.5s ease both;
  transition: transform 0.3s ease;
}

.result-item:hover {
  transform: translateX(10px);
}

.item-number {
  background: linear-gradient(45deg, #ff6b6b, #ff9ff3);
  width: 30px;
  height: 30px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  margin-right: 1rem;
  font-size: 0.9rem;
}

.item-text {
  color: white;
  flex: 1;
}

.item-glow {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(45deg, transparent, rgba(72, 219, 251, 0.1), transparent);
  border-radius: 12px;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.result-item:hover .item-glow {
  opacity: 1;
}

/* Animations */
@keyframes gradientShift {
  0%, 100% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

@keyframes cardAppear {
  from { opacity: 0; transform: scale(0.9) translateY(30px); }
  to { opacity: 1; transform: scale(1) translateY(0); }
}

@keyframes pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.05); }
}

@keyframes sparkle {
  0%, 100% { opacity: 0; transform: scale(0); }
  50% { opacity: 1; transform: scale(1); }
}

@keyframes orbFloat {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-20px); }
}

@keyframes fadeInOut {
  0%, 100% { opacity: 0.5; }
  50% { opacity: 1; }
}

@keyframes slideUp {
  from { opacity: 0; transform: translateY(30px); }
  to { opacity: 1; transform: translateY(0); }
}

@keyframes itemAppear {
  from { opacity: 0; transform: translateX(-30px); }
  to { opacity: 1; transform: translateX(0); }
}

/* Responsive */
@media (max-width: 768px) {
  .main-content {
    padding: 1rem;
  }

  .magic-title {
    font-size: 2.5rem;
  }

  .futuristic-card {
    padding: 2rem 1.5rem;
  }
}
</style>
