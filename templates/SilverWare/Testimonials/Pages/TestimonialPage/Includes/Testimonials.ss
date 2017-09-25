<% if $EnabledTestimonials %>
  <div class="testimonials">
    <% loop $EnabledTestimonials %>
      <% include SilverWare\Testimonials\Model\Testimonial %>
    <% end_loop %>
  </div>
<% else %>
  <% include Alert Type='warning', Text=$NoDataMessage %>
<% end_if %>
