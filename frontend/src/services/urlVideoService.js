import api from './api';

export const urlVideoService = {
    async generateList(url) {
        try {
            const response = await api.post('/video/analyze', {
                url: url
            });
            return response.data;
        } catch (error) {
            throw this.handleError(error);
        }
    },

    handleError(error) {
        if (error.response?.data?.message) {
            return new Error(error.response.data.message);
        }

        if (error.code === 'NETWORK_ERROR') {
            return new Error('Problème de connexion au serveur');
        }

        return new Error('Une erreur est survenue');
    }
};
