<template>
  <div :id="'reply-'+id" class="card my-3">
    <div class="card-header" :class="isBest ? ' bg-success' : 'bg-default'">
      <div class="level">
        <h5 class="flex">
          <a :href="'/profiles/'+data.owner.name" v-text="data.owner.name"></a>
          said
          <span v-text="ago"></span>
        </h5>

        <div v-if="$signedIn">
          <favorite :reply="data"></favorite>
        </div>
      </div>
    </div>

    <div class="card-body">
      <div v-if="editting">
        <form @submit="update">
          <div class="form-group">
            <textarea class="form-control" v-model="body" required></textarea>
          </div>

          <button class="btn btn-xs btn-primary">Update</button>
          <button class="btn btn-xs btn-link" @click="editting = false" type="button">Cancel</button>
        </form>
      </div>
      <div v-else v-html="body"></div>
    </div>

    <div class="card-footer level">
      <div v-if="$authorize('updateReply', reply)">
        <button class="btn btn-xs btn-success mr-1" @click="editting = true">Edit</button>
        <button class="btn btn-xs btn-danger mr-1" @click="destroy">Delete</button>
      </div>
      <button
        class="btn btn-xs btn-secondary ml-a"
        @click="markBestReply"
        v-show="!isBest"
      >Best Reply?</button>
    </div>
  </div>
</template>
<script>
import Favorite from "./Favorite.vue";
import moment from "moment";

export default {
  props: ["data"],

  components: { Favorite },

  data() {
    return {
      editting: false,
      id: this.data.id,
      body: this.data.body,
      isBest: this.data.isBest,
      reply: this.data
    };
  },

  computed: {
    ago() {
      return moment(this.data.created_at).fromNow() + "...";
    }
  },

  created() {
    window.events.$on("best-reply-selected", id => {
      this.isBest = id === this.id;
    });
  },

  methods: {
    update() {
      axios
        .patch("/replies/" + this.data.id, {
          body: this.body
        })
        .catch(error => {
          flash(error.response.data, "danger");
        });

      this.editting = false;

      flash("Updated!");
    },

    destroy() {
      axios.delete("/replies/" + this.data.id);

      this.$emit("deleted", this.data.id);
    },

    markBestReply() {
      axios.post("/replies/" + this.id + "/best");

      window.events.$emit("best-reply-selected", this.id);
    }
  }
};
</script>
