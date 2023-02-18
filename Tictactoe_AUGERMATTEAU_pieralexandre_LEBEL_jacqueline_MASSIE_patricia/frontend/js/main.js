import { createApp, ref } from 'https://unpkg.com/vue@3/dist/vue.esm-browser.js'
import urls_remote from 'http://jduranleau.cpsw-fcsei.com/module5/js/tic-tac-toe/frontend/js/urls.js'

const urls = [
    ["Code local", "../backend/ai.php"],
].concat(urls_remote)

const ordi1 = ref(urls[0][1]) // v-model
const ordi2 = ref(urls[1][1]) // v-model

const grille = ref([
    "", "", "",
    "", "", "",
    "", "", "",
])

const delai = ref(0) // v-model
const delai_fin = ref(1000) // v-model
let timeout_tour_ordi = null

const en_cours_de_jeu = ref(false)

const validation = ref({})

const victoires = ref({
    x: 0,
    o: 0,
    match_nul: 0,
})

function debuter() {
    victoires.value.x = 0
    victoires.value.o = 0
    victoires.value.match_nul = 0

    en_cours_de_jeu.value = true
    jouerRound()
}

function arreter() {
    en_cours_de_jeu.value = false

    grille.value = [
        "", "", "",
        "", "", "",
        "", "", ""
    ]

    clearTimeout(timeout_tour_ordi)
}

function jouerRound() {
    jouerOrdi(ordi1.value, "x", () => {
        jouerOrdi(ordi2.value, "o", () => {
            jouerRound()
        })
    })
}

function jouerOrdi(url, pion, callback) {
    if (en_cours_de_jeu.value == false) {
        return
    }

    const post = new FormData()
    post.set("grille", JSON.stringify(grille.value))
    post.set("pion", pion)

    const options = { method: "POST", body: post }

    fetch(url, options).then(resp => resp.json()).then(reponse => {
        timeout_tour_ordi = setTimeout(() => {
            // Vérification supplémentaire dans le cas ou le navigateur ne fait pas le clearTimeout assez vite...
            if (en_cours_de_jeu.value == false) {
                return
            }

            // === Update de la grille
            validation.value = tourValide(grille.value, reponse)
            validation.value.joueur = pion

            if (validation.value.success == false) {
                return
            }

            grille.value = reponse

            if (estFini()) {
                timeout_tour_ordi = setTimeout(() => {
                    finPartie()
                }, delai_fin.value)
            } else {
                callback()
            }
        }, delai.value)
    })
}

function tourValide(original, nouvelle) {
    // === ajout de cases? wtf...
    if (nouvelle.length != 9) {
        return {
            success: false,
            triche: true,
            erreur: "Un tic-tac-toe devrait avoir 9 cases...",
        }
    }

    // === jouer sur une case déjà utilisée
    for (let i = 0; i < original.length; i++) {
        if (original[i] != "" && original[i] != nouvelle[i]) {
            return {
                success: false,
                triche: true,
                erreur: `A remplacé la case ${i}`,
            }
        }
    }

    // === jouer plusieurs cases ou joue aucune case
    let cases_disponibles = []

    for (let i = 0; i < original.length; i++) {
        if (original[i] == "") {
            cases_disponibles.push(i)
        }
    }

    let nombre_cases_jouees = 0

    for (let index of cases_disponibles) {
        if (nouvelle[index] != "") {
            nombre_cases_jouees += 1
        }
    }

    if (nombre_cases_jouees == 0) {
        return {
            success: false,
            triche: false,
            erreur: "Aucune case jouée"
        }
    }

    if (nombre_cases_jouees > 1) {
        return {
            success: false,
            triche: true,
            erreur: `Trop de cases jouées (${nombre_cases_jouees})`
        }
    }

    return {
        success: true,
        triche: false,
        erreur: ""
    }
}

function estFini() {
    /*
    0 1 2
    3 4 5
    6 7 8
    */
    const lignes = [
        // Horizontales
        [0, 1, 2],
        [3, 4, 5],
        [6, 7, 8],
        // Verticales
        [0, 3, 6],
        [1, 4, 7],
        [2, 5, 8],
        // Diagonales
        [0, 4, 8],
        [2, 4, 6],
    ]

    // === Recherche du gagnant
    for (let ligne of lignes) {
        // ligne = [2,5,8]
        let texte_ligne = grille.value[ligne[0]] + grille.value[ligne[1]] + grille.value[ligne[2]]

        if (texte_ligne == "xxx") {
            return "x"
        } else if (texte_ligne == "ooo") {
            return "o"
        }
    }

    // === Vérification du match null

    const cases_disponibles = grille.value.filter(pion => pion == "")

    if (cases_disponibles.length == 0) {
        return "match_nul"
    }

    // === Sinon, la partie continue

    return false
}

function finPartie() {
    const gagnant = estFini()

    victoires.value[gagnant] += 1

    grille.value = [
        "", "", "",
        "", "", "",
        "", "", ""
    ]

    jouerRound()
}

function pourcentageVictoire(n_victoires) {
    const n_parties_total = victoires.value.x + victoires.value.o + victoires.value.match_nul

    if (n_parties_total == 0) {
        return 0
    } else {
        return Math.round((n_victoires / n_parties_total) * 100)
    }
}

const root = {
    setup() {
        return {
            // Propriétés
            grille,
            delai,
            delai_fin,
            ordi1,
            ordi2,
            victoires,
            en_cours_de_jeu,
            validation,
            urls,

            // Méthodes
            debuter,
            arreter,
            pourcentageVictoire
        }
    }
}

createApp(root).mount("#app")