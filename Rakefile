require 'html/proofer'

task :test do
  sh "bundle exec jekyll build"
  HTML::Proofer.new("./_site", {
  	:disable_external => true,
  	:file_ignore => [/_site\/docs\/.*?\/api/],
  }).run
end
