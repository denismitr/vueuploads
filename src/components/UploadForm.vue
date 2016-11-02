<template>
    <div>
        <h1>Upload Form - {{ isDraggedOver }}</h1>

        <div
            class="dragndrop"
            v-bind:class="{'dragndrop--dragged': isDraggedOver}"
            @dragover.prevent="enter"
            @dragenter.prevent="enter"
            @dragleave.prevent="leave"
            @dragend.prevent="leave"
            @drop.prevent="drop"
        >
            <input
                @change="selectFiles"
                type="file"
                name="files[]"
                class="dragndrop__input"
                id="file"
                ref="input"
                multiple
            >
            <label for="file" class="dragndrop__label" :class="{ 'dragndrop__label--compact': files.length > 0 }">
                <strong>Drag files here</strong> or click to select files
            </label>

            <uploads :files="files"></uploads>
        </div>
    </div>
</template>

<script>
    import Uploads from './Uploads';
    import eventHub from '../events.js';

    export default {
        data() {
            return {
                files: [],
                isDraggedOver: false
            }
        },

        components: {
            Uploads
        },

        methods: {
            enter() {
                this.isDraggedOver = true;
            },

            leave() {
                this.isDraggedOver = false;
            },

            drop(evt) {
                this.leave();

                this.addFiles(evt.dataTransfer.files);
            },

            selectFiles(evt) {
                this.addFiles(this.$refs.input.files);
            },

            addFiles(files) {
                var ii, file;

                for (ii = 0; ii < files.length; ii++) {
                    file = files[ii];

                    this.storeMeta(file).then((fileObject) => {
                        this.upload(fileObject);

                        console.log(fileObject.id);
                    }, (fileObject) => {
                        console.log(fileObject.failed);
                    });
                }
            },

            upload (fileObject) {
                var form = new FormData();

                form.append('file', fileObject.file);
                form.append('id', fileObject.id);

                eventHub.$emit('init');

                this.$http.post('http://localhost:3333/upload.php', form, {
                    before: (xhr) => {
                        fileObject.xhr = xhr;
                    },
                    progress: (evt) => {
                        //emit progress
                        eventHub.$emit('progress', fileObject, evt);
                    }
                }).then((response) => {
                    eventHub.$emit('finished', fileObject);
                }, (error) => {
                    eventHub.$emit('failed', fileObject);
                });
            },

            storeMeta(file) {
                var fileObject = this.generateFileObject(file);


                return new Promise((resolve, reject) => {
                    this.$http.post('http://localhost:3333/store.php', {
                        name: file.name
                    }).then((response) => {
                        fileObject.id = response.body.data.id;
                        resolve(fileObject);
                    }, (error) => {
                        fileObject.failed = true;
                        reject(fileObject);
                    })
                });
            },

            generateFileObject (file) {
                let fileObjectIndex = this.files.push({
                    id: null,
                    file: file,
                    progress: 0,
                    failed: false,
                    loadedBytes: 0,
                    totalBytes: 0,
                    timeStarted: (new Date).getTime(),
                    secondsRemaining: 0,
                    finished: false,
                    cancelled: false,
                    xhr: null
                }) - 1;

                return this.files[fileObjectIndex];
            }
        }
    }
</script>

<style scoped>
    .dragndrop {
        --dragndrop-min-height: 100px;
        width: 100%;
        min-height: var(--dragndrop-min-height);
        background-color: #f8f8f8;
        position: relative;
        border: 3px dashed rgba(0,0,0,0.3);
    }

    .dragndrop--dragged {
        border-color: #333;
    }

    .dragndrop__input {
        display: none;
    }

    .dragndrop__label {
        display: block;
        font-size: 1.1em;
        color: #555;
        vertical-align: middle;
        text-align: center;
        margin: calc(var(--dragndrop-min-height) / 2) 20px;
    }

    .dragndrop__label:hover {
        text-decoration: underline;
        cursor: pointer;
    }

    .dragndrop__label--compact {
        margin: calc(var(--dragndrop-min-height) / 4) 20px;
    }
</style>