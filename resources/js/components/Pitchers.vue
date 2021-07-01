<template>
    <div>
        <v-data-table>
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Name</th>
                    <th>Age</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="pitcher in data" :key="pitcher.id">
                    <td>{{ pitcher.player.name }}</td>
                    <td>{{ pitcher.player.name }}</td>
                    <td>{{ pitcher.player.age }}</td>
                </tr>
            </tbody>
        </v-data-table>
    </div>
</template>

<script>
    export default {
        props: ['data'],

        mounted() {
            console.log('some-vue-component mounted.');
            console.log(this.data)
        },
    }
</script>
