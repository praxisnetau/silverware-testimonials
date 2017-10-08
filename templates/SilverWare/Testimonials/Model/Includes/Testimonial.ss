<article class="testimonial $EvenOdd">
  <div class="content">
    $ContentOrSummary
  </div>
  <footer class="d-flex justify-content-end">
    <div class="text">
      <div class="author">
        $Author
      </div>
      <% if $PositionShown %>
        <div class="position">
          $Position
        </div>
      <% end_if %>
      <% if $OrganisationShown %>
        <div class="organisation">
          $Organisation
        </div>
      <% end_if %>
      <% if $DateShown %>
        <div class="date">
          $DateFormatted
        </div>
      <% end_if %>
    </div>
    <% if $HasMetaImage %>
      <div class="image">
        <img src="$MetaImageResized.URL" class="$ImageClass">
      </div>
    <% end_if %>
  </footer>
</article>
