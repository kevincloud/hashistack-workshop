job "buyer" {
  datacenters = ["dc1"]

  type = "batch"

  periodic {
    cron = "*/30 * * * *"
    prohibit_overlap = true
    time_zone = "America/New_York"
  }

  group "analysis" {
    # TODO: add docker volume dumps for mongo and other dbs
    #task "docker-volumes" {
    #}

    # TODO: add more robustness (i.e. a gutil task to backup to bucket)
    task "consul" {
      driver = "exec"

      config {
        command = "/bin/consul"
        args = [
          "snapshot",
          "save",
          "backup.snap"
        ]
      }
    }
  }
}
