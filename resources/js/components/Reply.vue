<template>
  <div :id="'reply-'+id" class="card my-3">
    <div class="card-header">
      <div class="level">
        <h5 class="flex">
          <a :href="'/profiles/'+data.owner.name" v-text="data.owner.name"></a>
          said {{ data.created_at }}...
        </h5>

        <!--@if (Auth::check())
                    <div>
                        <favorite :reply="{{ $reply }}"></favorite>
                    </div>
                @endif
        -->
      </div>
    </div>

    <div class="card-body">
      <div v-if="editting">
        <div class="form-group">
          <textarea class="form-control" v-model="body"></textarea>
        </div>

        <button class="btn btn-xs btn-primary" @click="update">Update</button>
        <button class="btn btn-xs btn-link" @click="editting = false">Cancel</button>
      </div>
      <div v-else v-text="body"></div>
    </div>@can('update', $reply)
    <div class="card-footer level">
      <button class="btn btn-success btn-xs mr-1" @click="editting = true">Edit</button>
      <button class="btn btn-danger btn-xs" @click="destroy">Delete</button>
    </div>@endcan
  </div>
</template>
<script>
import Favorite from "./Favorite.vue";

export default {
  props: ["data"],

  components: { Favorite },

  data() {
    return {
      editting: false,
      id: this.data.id,
      body: this.data.body
    };
  },

  methods: {
    update() {
      axios.patch("/replies/" + this.data.id, {
        body: this.body
      });

      this.editting = false;

      flash("Updated!");
    },

    destroy() {
      axios.delete("/replies/" + this.data.id);

      $(this.$el).fadeOut(300, () => {
        flash("Your reply has been deleted.");
      });
    }
  }
};
</script>
