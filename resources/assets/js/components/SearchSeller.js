import firebase from 'firebase'

Vue.component('search-seller', {
    props: ['sellers'],

    data() {
        return {
            researchedSeller: [],
            hideResearched: false
        }
    },

    mounted() {
        const database = firebase.database();

        database.ref('/seller-list').on('value', snapshot => {
            this.researchedSeller = snapshot.val() || [];
        })
    },

    methods: {
        isResearched(username) {
            return _.includes(this.researchedSeller, username)
        },
        isHidden(username) {
            return this.hideResearched && this.isResearched(username)
        }
    }
});