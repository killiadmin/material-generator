import api from './api';

export const videoPreviewService = {
    /**
     * Fetches a preview of the video from the given URL by making a POST request to the API.
     *
     * @param {string} url
     * @return {Promise<Object>}
     * @throws {Error}
     */
    async fetchPreview(url) {
        try {
            const response = await api.post('/video/preview', { url });
            return response.data;
        } catch (error) {
            throw this.handleError(error);
        }
    },

    handleError(error) {
        if (error.response?.data?.error) {
            return new Error(error.response.data.error);
        }

        if (error.response?.data?.message) {
            return new Error(error.response.data.message);
        }

        if (error.code === 'NETWORK_ERROR') {
            return new Error('Problème de connexion au serveur.');
        }

        return new Error('Erreur inconnue lors de la récupération de la vidéo.');
    }
};
