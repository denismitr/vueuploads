<template>
    <div>
        <div class="dragndrop__status" v-if="files.length">
            <ul class="list-inline">
                <li class="list-inline__item">Files: {{ files.length }}</li>
                <li class="list-inline__item">Percentage: {{ overallProgress }}%</li>
                <li class="list-inline__item list-inline__item--last">Time remaining: {{ timeRemaining }}</li>
            </ul>
        </div>
        <file v-for="file in files" :file="file"></file>
    </div>
</template>

<script>
    import File from './File';
    import eventHub from '../events.js';
    import timeremaining from '../helpers/time-remaining.js';
    import pad from '../helpers/pad.js';

    export default {
        data() {
            return {
                overallProgress: 0,
                interval: null,
                secondsRemaining: 0,
                timeRemaining: 'Calculating...'
            }
        },

        props: [
            'files'
        ],

        components: {
            File
        },

        mounted() {
            eventHub.$on('progress', this.updateOverallProgress);

            eventHub.$on('init', () => {
                if ( ! this.interval ) {
                    this.interval = setInterval(() => {
                        this.updateTimeRemaining();
                    }, 1000);
                }
            });
        },

        methods: {
            unfinishedFiles() {
                var ii, files = [];

                for (ii = 0; ii < this.files.length; ii++) {
                    if ( this.files[ii].finished || this.files[ii].cancelled ) {
                        continue;
                    }

                    files.push(this.files[ii]);
                }

                return files;
            },

            updateOverallProgress (fileObject, evt) {
                var unfinishedFiles = this.unfinishedFiles(), totalProgress = 0;

                unfinishedFiles.forEach((file) => {
                    totalProgress += file.progress;
                });

                this.overallProgress = parseInt(totalProgress / unfinishedFiles.length || 0);
            },

            updateTimeRemaining() {
                var minutes, seconds;

                this.secondsRemaining = 0;

                this.unfinishedFiles().forEach((file) => {
                    file.secondsRemaining = timeremaining.calculate(
                        file.totalBytes,
                        file.loadedBytes,
                        file.timeStarted
                    );

                    this.secondsRemaining += file.secondsRemaining;
                });

                minutes = Math.floor( this.secondsRemaining / 60 );
                seconds = this.secondsRemaining - minutes;

                this.timeRemaining = pad.left('00', minutes) + ':' + pad.left('00', seconds);
            }
        }
    }
</script>